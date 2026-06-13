<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Mine\AppStore\Enums\PluginStatus;
use Mine\AppStore\Repository\PluginStateRepository;
use Mine\AppStore\Repository\RuntimeManifestRepository;

beforeEach(function () {
    $this->basePath = appstore_temp_path();
    $this->app = appstore_application($this->basePath);
    $this->pluginsPath = (string) config('mine-plugins.paths.plugins');
    $this->statePath = (string) config('mine-plugins.paths.state');
    $this->cachePath = (string) config('mine-plugins.paths.cache');
    $this->files = new Filesystem();
});

afterEach(function () {
    appstore_delete_path($this->basePath);
});

test('service provider binds lifecycle services and boots enabled runtime plugins', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');
    appstore_service($this->app)->enable('acme/alpha');

    appstore_register_runtime_provider($this->app);

    expect($this->app->bound(\Mine\AppStore\Service\PluginLifecycleService::class))->toBeTrue()
        ->and($this->app->bound(\Mine\AppStore\Support\PluginAutoloader::class))->toBeTrue()
        ->and($this->app->make(appstore_binding('acme/alpha', 'registered')))->toBeTrue()
        ->and($this->app->make(appstore_binding('acme/alpha', 'booted')))->toBeTrue();
});

test('service provider marks malformed runtime manifests as failed during register', function () {
    (new RuntimeManifestRepository($this->files, $this->cachePath))->write([
        [
            'name' => 'acme/bad-runtime',
            'provider' => '',
            'autoload' => ['psr-4' => []],
        ],
    ]);

    appstore_register_runtime_provider($this->app);

    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);
    $runtime = require $this->cachePath;

    expect($state['acme/bad-runtime']['status'])->toBe(PluginStatus::Failed->value)
        ->and($state['acme/bad-runtime']['last_error']['stage'])->toBe('register')
        ->and($runtime)->toBe([]);
});

test('service provider isolates boot failures and removes failed plugins from runtime cache', function () {
    appstore_write_plugin(
        $this->pluginsPath,
        'acme/broken-boot',
        bootBody: "throw new \\RuntimeException('boot exploded');"
    );

    appstore_service($this->app)->enable('acme/broken-boot');
    appstore_register_runtime_provider($this->app);

    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);
    $runtime = require $this->cachePath;

    expect($state['acme/broken-boot']['status'])->toBe(PluginStatus::Failed->value)
        ->and($state['acme/broken-boot']['last_error']['stage'])->toBe('boot')
        ->and($state['acme/broken-boot']['last_error']['message'])->toBe('boot exploded')
        ->and($runtime)->toBe([]);
});

test('service provider rethrows runtime failures when configured', function () {
    appstore_write_plugin(
        $this->pluginsPath,
        'acme/strict-broken',
        bootBody: "throw new \\RuntimeException('strict boot exploded');"
    );

    appstore_service($this->app)->enable('acme/strict-broken');
    config()->set('mine-plugins.runtime.throw_on_failure', true);

    expect(fn () => appstore_register_runtime_provider($this->app))
        ->toThrow(RuntimeException::class, 'strict boot exploded');
});

test('service provider accepts providers with bindings and singletons properties', function () {
    appstore_write_plugin(
        $this->pluginsPath,
        'acme/with-bindings',
        registerBody: "\$this->app->instance('".appstore_binding('acme/with-bindings', 'registered')."', true);"
    );

    $providerPath = $this->pluginsPath.'/acme/with-bindings/src/PluginServiceProvider.php';
    $this->files->put($providerPath, <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace Plugin\Acme\WithBindings;

        use Illuminate\Support\ServiceProvider;

        class BoundString {}

        class SingletonString {}

        class PluginServiceProvider extends ServiceProvider
        {
            public array $bindings = [
                'bound-string' => BoundString::class,
            ];

            public array $singletons = [
                'singleton-string' => SingletonString::class,
            ];

            public function register(): void
            {
                $this->app->instance('mine-plugin.acme.with.bindings.registered', true);
            }

            public function boot(): void
            {
                $this->app->instance('mine-plugin.acme.with.bindings.booted', true);
            }
        }
        PHP);

    appstore_service($this->app)->enable('acme/with-bindings');
    appstore_register_runtime_provider($this->app);

    expect($this->app->bound('bound-string'))->toBeTrue()
        ->and($this->app->bound('singleton-string'))->toBeTrue();
});

test('state repository failure writes can be used by runtime provider without previous state', function () {
    (new PluginStateRepository($this->files, $this->statePath))->markFailed(
        'acme/manual',
        ['name' => 'acme/manual'],
        'register',
        new RuntimeException('manual failure')
    );

    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);

    expect($state['acme/manual']['status'])->toBe(PluginStatus::Failed->value)
        ->and($state['acme/manual']['last_error']['message'])->toBe('manual failure');
});
