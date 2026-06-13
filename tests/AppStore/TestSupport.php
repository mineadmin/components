<?php

declare(strict_types=1);

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Mine\AppStore\AppStoreServiceProvider;
use Mine\AppStore\Repository\PluginManifestRepository;
use Mine\AppStore\Repository\PluginStateRepository;
use Mine\AppStore\Repository\RuntimeManifestRepository;
use Mine\AppStore\Service\PluginLifecycleService;
use Mine\AppStore\Support\PluginAutoloader;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

final class AppStoreFakeKernel implements Kernel
{
    /**
     * @var array<int, array{command: string, parameters: array<string, mixed>}>
     */
    public array $calls = [];

    public function bootstrap(): void {}

    public function handle($input, $output = null): int
    {
        return 0;
    }

    public function terminate($input, $status): void {}

    public function call($command, array $parameters = [], $outputBuffer = null): int
    {
        $this->calls[] = [
            'command' => (string) $command,
            'parameters' => $parameters,
        ];

        return 0;
    }

    public function queue($command, array $parameters = []): mixed
    {
        return null;
    }

    public function all(): array
    {
        return [];
    }

    public function output(): string
    {
        return '';
    }
}

final class AppStoreFakeLifecycleService extends PluginLifecycleService
{
    /**
     * @param  array<int, array<string, mixed>>  $discoverResult
     * @param  array<int, array<string, mixed>>  $cacheResult
     * @param  array<int, array<string, mixed>>  $doctorResult
     */
    public function __construct(
        public array $discoverResult = [],
        public array $cacheResult = [],
        public array $doctorResult = [],
        public ?Throwable $throwOnEnable = null,
        public ?Throwable $throwOnDisable = null,
        public ?Throwable $throwOnCache = null,
        public array $enabled = [],
        public array $disabled = [],
        public bool $cleared = false,
    ) {}

    public function discover(): array
    {
        return $this->discoverResult;
    }

    public function enable(string $name): void
    {
        if ($this->throwOnEnable !== null) {
            throw $this->throwOnEnable;
        }

        $this->enabled[] = $name;
    }

    public function disable(string $name): void
    {
        if ($this->throwOnDisable !== null) {
            throw $this->throwOnDisable;
        }

        $this->disabled[] = $name;
    }

    public function cache(): array
    {
        if ($this->throwOnCache !== null) {
            throw $this->throwOnCache;
        }

        return $this->cacheResult;
    }

    public function clear(): void
    {
        $this->cleared = true;
    }

    public function doctor(?string $name = null): array
    {
        if ($name === null) {
            return $this->doctorResult;
        }

        return array_values(array_filter(
            $this->doctorResult,
            static fn (array $plugin): bool => ($plugin['name'] ?? null) === $name
        ));
    }
}

function appstore_temp_path(string $prefix = 'appstore-test-'): string
{
    $path = sys_get_temp_dir().'/'.$prefix.bin2hex(random_bytes(8));

    (new Filesystem())->ensureDirectoryExists($path);

    return $path;
}

function appstore_delete_path(string $path): void
{
    (new Filesystem())->deleteDirectory($path);
}

/**
 * @param  array<string, mixed>  $config
 */
function appstore_application(string $basePath, array $config = []): Application
{
    $filesystem = new Filesystem();
    $filesystem->ensureDirectoryExists($basePath.'/storage');
    $filesystem->ensureDirectoryExists($basePath.'/bootstrap/cache');

    $app = new Application($basePath);
    $app->useStoragePath($basePath.'/storage');
    $app->instance(Filesystem::class, $filesystem);
    $app->instance('files', $filesystem);
    $app->instance('config', new ConfigRepository(array_replace_recursive([
        'mine-plugins' => [
            'paths' => [
                'plugins' => $basePath.'/plugins',
                'state' => $basePath.'/storage/app/mine-plugins/state.json',
                'cache' => $basePath.'/bootstrap/cache/mine_plugins.php',
            ],
            'runtime' => [
                'throw_on_failure' => false,
            ],
        ],
    ], $config)));
    $app->instance(Kernel::class, new AppStoreFakeKernel());

    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    return $app;
}

function appstore_service(ApplicationContract $app): PluginLifecycleService
{
    return new PluginLifecycleService(
        $app,
        new PluginManifestRepository($app->make(Filesystem::class), (string) config('mine-plugins.paths.plugins')),
        new PluginStateRepository($app->make(Filesystem::class), (string) config('mine-plugins.paths.state')),
        new RuntimeManifestRepository($app->make(Filesystem::class), (string) config('mine-plugins.paths.cache')),
        new PluginAutoloader()
    );
}

function appstore_register_runtime_provider(ApplicationContract $app): AppStoreServiceProvider
{
    $provider = new AppStoreServiceProvider($app);
    $provider->register();
    $provider->boot();

    return $provider;
}

/**
 * @param  array<int, string>  $dependencies
 * @param  array<string, mixed>  $overrides
 */
function appstore_write_plugin(
    string $pluginsPath,
    string $name,
    array $dependencies = [],
    ?string $providerClass = null,
    bool $writeProvider = true,
    bool $providerExtendsServiceProvider = true,
    string $registerBody = '',
    string $bootBody = '',
    array $overrides = [],
): array {
    $filesystem = new Filesystem();
    [$vendor, $plugin] = explode('/', $name, 2);
    $pluginPath = $pluginsPath.'/'.$vendor.'/'.$plugin;
    $providerClass ??= appstore_provider_class($name);
    $namespace = Str::beforeLast($providerClass, '\\');
    $class = Str::afterLast($providerClass, '\\');

    $filesystem->ensureDirectoryExists($pluginPath.'/src');

    $manifest = array_replace([
        'name' => $name,
        'version' => '1.0.0',
        'description' => 'Test plugin',
        'author' => 'mineadmin',
        'provider' => $providerClass,
        'autoload' => [
            'psr-4' => [
                $namespace.'\\' => 'src/',
            ],
        ],
        'require' => [
            'plugins' => $dependencies,
            'php' => '^8.4',
            'laravel' => '^13.0',
        ],
    ], $overrides);

    $filesystem->put(
        $pluginPath.'/mine.json',
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
    );

    if ($writeProvider) {
        $extends = $providerExtendsServiceProvider ? ' extends ServiceProvider' : '';
        $use = $providerExtendsServiceProvider ? 'use Illuminate\Support\ServiceProvider;' : '';
        $registerBody = $registerBody !== '' ? $registerBody : "\$this->app->instance('".appstore_binding($name, 'registered')."', true);";
        $bootBody = $bootBody !== '' ? $bootBody : "\$this->app->instance('".appstore_binding($name, 'booted')."', true);";

        $filesystem->put($pluginPath.'/src/'.$class.'.php', <<<PHP
            <?php

            declare(strict_types=1);

            namespace {$namespace};

            {$use}

            class {$class}{$extends}
            {
                public function register(): void
                {
                    {$registerBody}
                }

                public function boot(): void
                {
                    {$bootBody}
                }
            }
            PHP);
    }

    return [
        'path' => $pluginPath,
        'provider' => $providerClass,
        'namespace' => $namespace,
    ];
}

function appstore_write_json(string $path, string $contents): void
{
    $filesystem = new Filesystem();
    $filesystem->ensureDirectoryExists(dirname($path));
    $filesystem->put($path, $contents);
}

function appstore_provider_class(string $name): string
{
    [$vendor, $plugin] = explode('/', $name, 2);

    return sprintf('Plugin\\%s\\%s\\PluginServiceProvider', Str::studly($vendor), Str::studly($plugin));
}

function appstore_binding(string $name, string $event): string
{
    return 'mine-plugin.'.str_replace(['/', '-'], '.', $name).'.'.$event;
}

/**
 * @param  array<int, array<string, mixed>>  $plugins
 * @return array<string, array<string, mixed>>
 */
function appstore_key_by_name(array $plugins): array
{
    $keyed = [];

    foreach ($plugins as $plugin) {
        $keyed[(string) $plugin['name']] = $plugin;
    }

    return $keyed;
}

function appstore_command_tester(Command $command, ApplicationContract $app, PluginLifecycleService $service): CommandTester
{
    $app->instance(PluginLifecycleService::class, $service);
    $command->setLaravel($app);

    $console = new ConsoleApplication('app-store-tests');
    $console->addCommand($command);

    return new CommandTester($console->find($command->getName() ?: ''));
}
