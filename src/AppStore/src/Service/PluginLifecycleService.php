<?php

declare(strict_types=1);

namespace Mine\AppStore\Service;

use Composer\Semver\Semver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Mine\AppStore\Enums\PluginStatus;
use Mine\AppStore\Exception\PluginException;
use Mine\AppStore\Repository\PluginManifestRepository;
use Mine\AppStore\Repository\PluginStateRepository;
use Mine\AppStore\Repository\RuntimeManifestRepository;
use Mine\AppStore\Support\PluginAutoloader;

class PluginLifecycleService
{
    public function __construct(
        private readonly Application $app,
        private readonly PluginManifestRepository $manifestRepository,
        private readonly PluginStateRepository $stateRepository,
        private readonly RuntimeManifestRepository $runtimeManifestRepository,
        private readonly PluginAutoloader $autoloader
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function discover(): array
    {
        $state = $this->stateRepository->all();

        return array_map(static function (array $plugin) use ($state): array {
            $name = (string) $plugin['name'];

            if (($plugin['errors'] ?? []) !== []) {
                $plugin['status'] = PluginStatus::Invalid->value;

                return $plugin;
            }

            $plugin['status'] = $state[$name]['status'] ?? PluginStatus::Discovered->value;
            $plugin['last_error'] = $state[$name]['last_error'] ?? null;

            return $plugin;
        }, $this->manifestRepository->discover());
    }

    public function enable(string $name): void
    {
        $plugin = $this->findPlugin($name);
        $this->validatePluginCanBeEnabled($plugin);
        $this->stateRepository->markEnabled($plugin);
        $this->cache();
        $this->clearApplicationCaches();
    }

    public function disable(string $name): void
    {
        $this->findPlugin($name);
        $dependents = $this->enabledDependents($name);

        if ($dependents !== []) {
            throw new \RuntimeException(sprintf(
                'Plugin [%s] cannot be disabled because it is required by: %s',
                $name,
                implode(', ', $dependents)
            ));
        }

        $this->stateRepository->markDisabled($name);
        $this->cache();
        $this->clearApplicationCaches();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cache(): array
    {
        $enabledPlugins = [];

        foreach ($this->discover() as $plugin) {
            if (($plugin['status'] ?? null) !== PluginStatus::Enabled->value) {
                continue;
            }

            try {
                $this->validatePluginCanRun($plugin);
                $enabledPlugins[] = $plugin;
            } catch (\Throwable $exception) {
                $this->stateRepository->markFailed((string) $plugin['name'], $plugin, 'cache', $exception);
            }
        }

        $enabledPlugins = $this->sortByDependencies($enabledPlugins);
        $runtimePlugins = array_map(fn (array $plugin): array => $this->runtimePlugin($plugin), $enabledPlugins);

        $this->runtimeManifestRepository->write($runtimePlugins);

        return $runtimePlugins;
    }

    public function clear(): void
    {
        $this->runtimeManifestRepository->clear();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function doctor(?string $name = null): array
    {
        $plugins = $this->discover();

        if ($name === null) {
            return $plugins;
        }

        return array_values(array_filter(
            $plugins,
            static fn (array $plugin): bool => ($plugin['name'] ?? null) === $name
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function findPlugin(string $name): array
    {
        foreach ($this->discover() as $plugin) {
            if (($plugin['name'] ?? null) === $name) {
                return $plugin;
            }
        }

        throw PluginException::notFound($name);
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function validatePluginCanBeEnabled(array $plugin): void
    {
        if (($plugin['errors'] ?? []) !== []) {
            throw PluginException::invalid((string) $plugin['name'], $plugin['errors']);
        }

        $this->validateVersionConstraints($plugin);
        $this->validateDependenciesAreEnabled($plugin);
        $this->validateProvider($plugin);
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function validatePluginCanRun(array $plugin): void
    {
        if (($plugin['errors'] ?? []) !== []) {
            throw PluginException::invalid((string) $plugin['name'], $plugin['errors']);
        }

        $this->validateVersionConstraints($plugin);
        $this->validateDependenciesAreEnabled($plugin);
        $this->validateProvider($plugin);
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function validateVersionConstraints(array $plugin): void
    {
        $phpConstraint = $plugin['require']['php'] ?? null;
        $laravelConstraint = $plugin['require']['laravel'] ?? null;

        if (is_string($phpConstraint) && $phpConstraint !== '' && ! Semver::satisfies(PHP_VERSION, $phpConstraint)) {
            throw new \RuntimeException(sprintf('Plugin [%s] requires PHP [%s].', $plugin['name'], $phpConstraint));
        }

        if (is_string($laravelConstraint) && $laravelConstraint !== '' && ! Semver::satisfies($this->app->version(), $laravelConstraint)) {
            throw new \RuntimeException(sprintf('Plugin [%s] requires Laravel [%s].', $plugin['name'], $laravelConstraint));
        }
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function validateDependenciesAreEnabled(array $plugin): void
    {
        foreach ($plugin['require']['plugins'] ?? [] as $dependency) {
            if ($this->stateRepository->status((string) $dependency) !== PluginStatus::Enabled) {
                throw new \RuntimeException(sprintf(
                    'Plugin [%s] depends on plugin [%s], but the dependency is not enabled.',
                    $plugin['name'],
                    $dependency
                ));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function validateProvider(array $plugin): void
    {
        $this->autoloader->addPsr4Mappings($plugin['autoload']['psr-4'] ?? []);

        $providerClass = (string) ($plugin['provider'] ?? '');

        if (! class_exists($providerClass)) {
            throw new \RuntimeException(sprintf('Plugin provider [%s] was not found.', $providerClass));
        }

        if (! is_subclass_of($providerClass, ServiceProvider::class)) {
            throw new \RuntimeException(sprintf('Plugin provider [%s] must extend [%s].', $providerClass, ServiceProvider::class));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $plugins
     * @return array<int, array<string, mixed>>
     */
    private function sortByDependencies(array $plugins): array
    {
        $pluginsByName = [];
        $sorted = [];
        $visiting = [];
        $visited = [];

        foreach ($plugins as $plugin) {
            $pluginsByName[(string) $plugin['name']] = $plugin;
        }

        $visit = function (string $name) use (&$visit, &$pluginsByName, &$sorted, &$visiting, &$visited): void {
            if (isset($visited[$name])) {
                return;
            }

            if (isset($visiting[$name])) {
                throw new \RuntimeException(sprintf('Circular plugin dependency detected at [%s].', $name));
            }

            if (! isset($pluginsByName[$name])) {
                return;
            }

            $visiting[$name] = true;

            foreach ($pluginsByName[$name]['require']['plugins'] ?? [] as $dependency) {
                $visit((string) $dependency);
            }

            unset($visiting[$name]);
            $visited[$name] = true;
            $sorted[] = $pluginsByName[$name];
        };

        foreach (array_keys($pluginsByName) as $name) {
            $visit($name);
        }

        return $sorted;
    }

    /**
     * @param  array<string, mixed>  $plugin
     * @return array<string, mixed>
     */
    private function runtimePlugin(array $plugin): array
    {
        return [
            'name' => $plugin['name'],
            'path' => $plugin['path'],
            'version' => $plugin['version'],
            'provider' => $plugin['provider'],
            'autoload' => $plugin['autoload'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function enabledDependents(string $name): array
    {
        $dependents = [];

        foreach ($this->discover() as $plugin) {
            if (($plugin['status'] ?? null) !== PluginStatus::Enabled->value) {
                continue;
            }

            if (in_array($name, $plugin['require']['plugins'] ?? [], true)) {
                $dependents[] = (string) $plugin['name'];
            }
        }

        return $dependents;
    }

    private function clearApplicationCaches(): void
    {
        try {
            $this->app->make(Kernel::class)->call('optimize:clear');
        } catch (\Throwable) {
            //
        }
    }
}
