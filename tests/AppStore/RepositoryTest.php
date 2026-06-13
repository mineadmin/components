<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Mine\AppStore\Enums\PluginStatus;
use Mine\AppStore\Repository\PluginManifestRepository;
use Mine\AppStore\Repository\PluginStateRepository;
use Mine\AppStore\Repository\RuntimeManifestRepository;

beforeEach(function () {
    $this->basePath = appstore_temp_path();
    $this->pluginsPath = $this->basePath.'/plugins';
    $this->statePath = $this->basePath.'/state/state.json';
    $this->runtimePath = $this->basePath.'/cache/mine_plugins.php';
    $this->files = new Filesystem();
    $this->files->ensureDirectoryExists($this->pluginsPath);
});

afterEach(function () {
    appstore_delete_path($this->basePath);
});

test('manifest repository returns an empty list when plugin root is missing', function () {
    $repository = new PluginManifestRepository($this->files, $this->basePath.'/missing');

    expect($repository->discover())->toBe([]);
});

test('manifest repository normalizes valid plugin manifests', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/alpha');

    $plugins = (new PluginManifestRepository($this->files, $this->pluginsPath))->discover();

    expect($plugins)->toHaveCount(1)
        ->and($plugins[0]['name'])->toBe('acme/alpha')
        ->and($plugins[0]['status'])->toBe(PluginStatus::Discovered->value)
        ->and($plugins[0]['autoload']['psr-4'])->toHaveKey('Plugin\\Acme\\Alpha\\')
        ->and($plugins[0]['autoload']['psr-4']['Plugin\\Acme\\Alpha\\'])->toBe(realpath($this->pluginsPath.'/acme/alpha/src'))
        ->and($plugins[0]['namespace'])->toBe('Plugin\\Acme\\Alpha')
        ->and($plugins[0]['errors'])->toBe([]);
});

test('manifest repository reports malformed plugin manifests without aborting discovery', function () {
    appstore_write_plugin($this->pluginsPath, 'acme/valid');
    appstore_write_json($this->pluginsPath.'/acme/bad-json/mine.json', '{bad');
    appstore_write_json($this->pluginsPath.'/acme/scalar/mine.json', '"oops"');
    appstore_write_plugin($this->pluginsPath, 'acme/mismatch', overrides: ['name' => 'other/name']);
    appstore_write_plugin($this->pluginsPath, 'acme/bad-name', overrides: ['name' => 'bad']);
    appstore_write_plugin($this->pluginsPath, 'acme/no-provider', overrides: ['provider' => '']);
    appstore_write_plugin($this->pluginsPath, 'acme/no-autoload', overrides: ['autoload' => ['psr-4' => []]]);
    appstore_write_plugin($this->pluginsPath, 'acme/bad-namespace', overrides: ['autoload' => ['psr-4' => ['Plugin\\Bad' => 'src/']]]);
    appstore_write_plugin($this->pluginsPath, 'acme/bad-path-type', overrides: ['autoload' => ['psr-4' => ['Plugin\\BadType\\' => ['src/']]]]);
    appstore_write_plugin($this->pluginsPath, 'acme/missing-path', overrides: ['autoload' => ['psr-4' => ['Plugin\\MissingPath\\' => 'missing/']]]);
    appstore_write_plugin($this->pluginsPath, 'acme/escaping-path', overrides: ['autoload' => ['psr-4' => ['Plugin\\EscapingPath\\' => '../']]]);
    appstore_write_plugin($this->pluginsPath, 'acme/bad-dependencies', overrides: ['require' => ['plugins' => 'acme/valid']]);
    appstore_write_plugin($this->pluginsPath, 'acme/bad-dependency-name', overrides: ['require' => ['plugins' => ['not-valid']]]);

    $plugins = appstore_key_by_name((new PluginManifestRepository($this->files, $this->pluginsPath))->discover());

    expect($plugins['acme/valid']['errors'])->toBe([])
        ->and($plugins['acme/bad-json']['errors'][0])->toContain('invalid JSON')
        ->and($plugins['acme/scalar']['errors'])->toContain('mine.json must contain a JSON object.')
        ->and($plugins['other/name']['errors'][0])->toContain('must match manifest name')
        ->and($plugins['bad']['errors'])->toContain('Plugin name must use vendor/name format.')
        ->and($plugins['acme/no-provider']['errors'])->toContain('Provider is required.')
        ->and($plugins['acme/no-autoload']['errors'])->toContain('autoload.psr-4 must define at least one namespace mapping.')
        ->and($plugins['acme/bad-namespace']['errors'])->toContain('PSR-4 namespace must be a string ending with a backslash.')
        ->and($plugins['acme/bad-path-type']['errors'][0])->toContain('must be a string')
        ->and($plugins['acme/missing-path']['errors'][0])->toContain('is invalid or escapes')
        ->and($plugins['acme/escaping-path']['errors'][0])->toContain('is invalid or escapes')
        ->and($plugins['acme/bad-dependencies']['errors'])->toContain('require.plugins must be an array.')
        ->and($plugins['acme/bad-dependency-name']['errors'])->toContain('Each plugin dependency must use vendor/name format.');
});

test('state repository reads and writes lifecycle state', function () {
    $repository = new PluginStateRepository($this->files, $this->statePath);
    $plugin = [
        'name' => 'acme/alpha',
        'version' => '1.2.3',
        'path' => $this->pluginsPath.'/acme/alpha',
        'provider' => 'Plugin\\Acme\\Alpha\\PluginServiceProvider',
    ];

    expect($repository->all())->toBe([])
        ->and($repository->status('acme/unknown'))->toBe(PluginStatus::Discovered);

    $repository->markEnabled($plugin);
    $state = $repository->all();

    expect($state['acme/alpha']['status'])->toBe(PluginStatus::Enabled->value)
        ->and($state['acme/alpha']['last_error'])->toBeNull()
        ->and($repository->status('acme/alpha'))->toBe(PluginStatus::Enabled);

    $repository->markDisabled('acme/alpha');

    expect($repository->all()['acme/alpha']['status'])->toBe(PluginStatus::Disabled->value)
        ->and($repository->all()['acme/alpha']['disabled_at'])->not->toBeNull();

    $repository->markFailed('acme/alpha', $plugin, 'boot', new RuntimeException('boom'));
    $state = $repository->all();

    expect($state['acme/alpha']['status'])->toBe(PluginStatus::Failed->value)
        ->and($state['acme/alpha']['last_error']['stage'])->toBe('boot')
        ->and($state['acme/alpha']['last_error']['class'])->toBe(RuntimeException::class)
        ->and($state['acme/alpha']['last_error']['message'])->toBe('boom');
});

test('state repository tolerates invalid state files', function () {
    appstore_write_json($this->statePath, '{bad');

    $repository = new PluginStateRepository($this->files, $this->statePath);

    expect($repository->all())->toBe([]);
});

test('runtime manifest repository writes removes clears and ignores non-array manifests', function () {
    $repository = new RuntimeManifestRepository($this->files, $this->runtimePath);

    expect($repository->all())->toBe([]);

    $repository->write([
        ['name' => 'acme/alpha', 'provider' => 'AlphaProvider'],
        ['name' => 'acme/beta', 'provider' => 'BetaProvider'],
    ]);

    expect($repository->all())->toHaveCount(2);

    $repository->remove('acme/alpha');

    expect($repository->all())->toBe([
        ['name' => 'acme/beta', 'provider' => 'BetaProvider'],
    ]);

    $repository->clear();

    expect($this->files->exists($this->runtimePath))->toBeFalse();

    appstore_write_json($this->runtimePath, "<?php\n\nreturn 'oops';\n");

    expect($repository->all())->toBe([]);
});
