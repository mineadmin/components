<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Mine\AppStore\Enums\PluginStatus;
use Mine\AppStore\Exception\PluginException;
use Mine\AppStore\Repository\PluginStateRepository;

beforeEach(function () {
    $this->basePath = appstore_temp_path();
    $this->app = appstore_application($this->basePath);
    $this->pluginsPath = (string) config('mine-plugins.paths.plugins');
    $this->statePath = (string) config('mine-plugins.paths.state');
    $this->cachePath = (string) config('mine-plugins.paths.cache');
    $this->files = new Filesystem();
    $this->service = appstore_service($this->app);
});

afterEach(function () {
    appstore_delete_path($this->basePath);
});

test('discover merges local state and preserves invalid manifests', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/enabled');
    appstore_write_plugin($this->pluginsPath, 'acme/invalid', overrides: ['provider' => '']);

    (new PluginStateRepository($this->files, $this->statePath))->markEnabled([
        'name' => 'acme/enabled',
        'version' => '1.0.0',
        'path' => $this->pluginsPath.'/acme/enabled',
        'provider' => appstore_provider_class('acme/enabled'),
    ]);

    $plugins = appstore_key_by_name($this->service->discover());

    expect($plugins['acme/enabled']['status'])->toBe(PluginStatus::Enabled->value)
        ->and($plugins['acme/enabled']['last_error'])->toBeNull()
        ->and($plugins['acme/invalid']['status'])->toBe(PluginStatus::Invalid->value);
});

test('enable validates the manifest and writes state and runtime cache', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');

    $this->service->enable('acme/alpha');

    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);
    $runtime = require $this->cachePath;

    expect($state['acme/alpha']['status'])->toBe(PluginStatus::Enabled->value)
        ->and($runtime)->toHaveCount(1)
        ->and($runtime[0]['name'])->toBe('acme/alpha')
        ->and($this->app->make(Kernel::class)->calls[0]['command'])->toBe('optimize:clear');
});

test('enable rejects unknown invalid incompatible and unloadable plugins', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/invalid', overrides: ['provider' => '']);
    appstore_write_plugin($this->pluginsPath, 'acme/php-version', overrides: ['require' => ['plugins' => [], 'php' => '>=99.0', 'laravel' => '^13.0']]);
    appstore_write_plugin($this->pluginsPath, 'acme/laravel-version', overrides: ['require' => ['plugins' => [], 'php' => '^8.4', 'laravel' => '^99.0']]);
    appstore_write_plugin($this->pluginsPath, 'acme/missing-provider', writeProvider: false);
    appstore_write_plugin($this->pluginsPath, 'acme/not-provider', providerExtendsServiceProvider: false);

    expect(fn () => $this->service->enable('acme/missing'))->toThrow(PluginException::class, 'was not found')
        ->and(fn () => $this->service->enable('acme/invalid'))->toThrow(PluginException::class, 'Provider is required')
        ->and(fn () => $this->service->enable('acme/php-version'))->toThrow(RuntimeException::class, 'requires PHP')
        ->and(fn () => $this->service->enable('acme/laravel-version'))->toThrow(RuntimeException::class, 'requires Laravel')
        ->and(fn () => $this->service->enable('acme/missing-provider'))->toThrow(RuntimeException::class, 'was not found')
        ->and(fn () => $this->service->enable('acme/not-provider'))->toThrow(RuntimeException::class, 'must extend');
});

test('dependencies must be enabled before a dependent plugin can be enabled', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');
    appstore_write_plugin($this->pluginsPath, 'acme/beta', dependencies: ['acme/alpha']);

    expect(fn () => $this->service->enable('acme/beta'))->toThrow(RuntimeException::class, 'dependency is not enabled');

    $this->service->enable('acme/alpha');
    $this->service->enable('acme/beta');

    $runtime = require $this->cachePath;

    expect(array_column($runtime, 'name'))->toBe(['acme/alpha', 'acme/beta']);
});

test('disable protects enabled dependencies and removes disabled plugins from runtime cache', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');
    appstore_write_plugin($this->pluginsPath, 'acme/beta', dependencies: ['acme/alpha']);

    $this->service->enable('acme/alpha');
    $this->service->enable('acme/beta');

    expect(fn () => $this->service->disable('acme/alpha'))->toThrow(RuntimeException::class, 'cannot be disabled');

    $this->service->disable('acme/beta');

    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);
    $runtime = require $this->cachePath;

    expect($state['acme/beta']['status'])->toBe(PluginStatus::Disabled->value)
        ->and(array_column($runtime, 'name'))->toBe(['acme/alpha']);
});

test('cache marks enabled plugins as failed when strict runtime validation fails', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/broken-cache', writeProvider: false);

    (new PluginStateRepository($this->files, $this->statePath))->markEnabled([
        'name' => 'acme/broken-cache',
        'version' => '1.0.0',
        'path' => $this->pluginsPath.'/acme/broken-cache',
        'provider' => appstore_provider_class('acme/broken-cache'),
    ]);

    $runtime = $this->service->cache();
    $state = json_decode((string) $this->files->get($this->statePath), true, 512, JSON_THROW_ON_ERROR);

    expect($runtime)->toBe([])
        ->and($state['acme/broken-cache']['status'])->toBe(PluginStatus::Failed->value)
        ->and($state['acme/broken-cache']['last_error']['stage'])->toBe('cache');
});

test('cache detects circular dependencies', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/one', dependencies: ['acme/two']);
    appstore_write_plugin($this->pluginsPath, 'acme/two', dependencies: ['acme/one']);

    $state = new PluginStateRepository($this->files, $this->statePath);

    foreach (['acme/one', 'acme/two'] as $name) {
        $state->markEnabled([
            'name' => $name,
            'version' => '1.0.0',
            'path' => $this->pluginsPath.'/'.$name,
            'provider' => appstore_provider_class($name),
        ]);
    }

    expect(fn () => $this->service->cache())->toThrow(RuntimeException::class, 'Circular plugin dependency');
});

test('doctor can return all plugins or a specific plugin and clear removes runtime cache', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');
    appstore_write_plugin($this->pluginsPath, 'acme/beta');

    $this->service->enable('acme/alpha');

    expect($this->service->doctor())->toHaveCount(2)
        ->and($this->service->doctor('acme/beta'))->toHaveCount(1)
        ->and($this->files->exists($this->cachePath))->toBeTrue();

    $this->service->clear();

    expect($this->files->exists($this->cachePath))->toBeFalse();
});
