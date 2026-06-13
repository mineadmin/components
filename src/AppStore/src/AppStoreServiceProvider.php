<?php

declare(strict_types=1);

namespace Mine\AppStore;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Mine\AppStore\Command\CacheCommand;
use Mine\AppStore\Command\ClearCommand;
use Mine\AppStore\Command\DisableCommand;
use Mine\AppStore\Command\DiscoverCommand;
use Mine\AppStore\Command\DoctorCommand;
use Mine\AppStore\Command\EnableCommand;
use Mine\AppStore\Repository\PluginManifestRepository;
use Mine\AppStore\Repository\PluginStateRepository;
use Mine\AppStore\Repository\RuntimeManifestRepository;
use Mine\AppStore\Service\PluginLifecycleService;
use Mine\AppStore\Support\PluginAutoloader;
use ReflectionProperty;
use Throwable;

class AppStoreServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array{plugin: array<string, mixed>, provider: ServiceProvider}>
     */
    private array $pluginProviders = [];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mine-plugins.php', 'mine-plugins');

        $this->app->singleton(PluginAutoloader::class);

        $this->app->singleton(PluginManifestRepository::class, function (Application $app): PluginManifestRepository {
            return new PluginManifestRepository(
                $app->make(Filesystem::class),
                (string) config('mine-plugins.paths.plugins')
            );
        });

        $this->app->singleton(PluginStateRepository::class, function (Application $app): PluginStateRepository {
            return new PluginStateRepository(
                $app->make(Filesystem::class),
                (string) config('mine-plugins.paths.state')
            );
        });

        $this->app->singleton(RuntimeManifestRepository::class, function (Application $app): RuntimeManifestRepository {
            return new RuntimeManifestRepository(
                $app->make(Filesystem::class),
                (string) config('mine-plugins.paths.cache')
            );
        });

        $this->app->singleton(PluginLifecycleService::class, function (Application $app): PluginLifecycleService {
            return new PluginLifecycleService(
                $app,
                $app->make(PluginManifestRepository::class),
                $app->make(PluginStateRepository::class),
                $app->make(RuntimeManifestRepository::class),
                $app->make(PluginAutoloader::class)
            );
        });

        $this->registerRuntimePlugins();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DiscoverCommand::class,
                EnableCommand::class,
                DisableCommand::class,
                DoctorCommand::class,
                CacheCommand::class,
                ClearCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/mine-plugins.php' => config_path('mine-plugins.php'),
            ], 'mine-plugins-config');
        }

        foreach ($this->pluginProviders as $name => $registered) {
            try {
                $this->app->call([$registered['provider'], 'boot']);
            } catch (Throwable $exception) {
                $this->markRuntimePluginAsFailed($name, $registered['plugin'], 'boot', $exception);
            }
        }
    }

    private function registerRuntimePlugins(): void
    {
        $runtimeManifest = $this->app->make(RuntimeManifestRepository::class);
        $autoloader = $this->app->make(PluginAutoloader::class);

        foreach ($runtimeManifest->all() as $plugin) {
            $name = (string) ($plugin['name'] ?? '');

            try {
                $providerClass = (string) ($plugin['provider'] ?? '');
                $autoload = $plugin['autoload']['psr-4'] ?? [];

                if ($name === '' || $providerClass === '' || ! is_array($autoload)) {
                    throw new \RuntimeException('Runtime plugin manifest is invalid.');
                }

                $autoloader->addPsr4Mappings($autoload);

                if (! class_exists($providerClass)) {
                    throw new \RuntimeException(sprintf('Plugin provider [%s] was not found.', $providerClass));
                }

                if (! is_subclass_of($providerClass, ServiceProvider::class)) {
                    throw new \RuntimeException(sprintf('Plugin provider [%s] must extend [%s].', $providerClass, ServiceProvider::class));
                }

                /** @var ServiceProvider $provider */
                $provider = new $providerClass($this->app);

                $this->registerProviderBindings($provider);
                $provider->register();

                $this->pluginProviders[$name] = [
                    'plugin' => $plugin,
                    'provider' => $provider,
                ];
            } catch (Throwable $exception) {
                $this->markRuntimePluginAsFailed($name, $plugin, 'register', $exception);
            }
        }
    }

    private function registerProviderBindings(ServiceProvider $provider): void
    {
        foreach ($this->providerProperty($provider, 'bindings') as $key => $value) {
            $this->app->bind($key, $value);
        }

        foreach ($this->providerProperty($provider, 'singletons') as $key => $value) {
            $this->app->singleton($key, $value);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function providerProperty(ServiceProvider $provider, string $property): array
    {
        if (! property_exists($provider, $property)) {
            return [];
        }

        $reflectionProperty = new ReflectionProperty($provider, $property);
        $value = $reflectionProperty->getValue($provider);

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    private function markRuntimePluginAsFailed(string $name, array $plugin, string $stage, Throwable $exception): void
    {
        if ($name !== '') {
            $this->app->make(PluginStateRepository::class)->markFailed($name, $plugin, $stage, $exception);
            $this->app->make(RuntimeManifestRepository::class)->remove($name);
        }

        if ((bool) config('mine-plugins.runtime.throw_on_failure', false)) {
            throw $exception;
        }
    }
}
