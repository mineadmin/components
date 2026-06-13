<?php

declare(strict_types=1);

use Mine\AppStore\Command\CacheCommand;
use Mine\AppStore\Command\ClearCommand;
use Mine\AppStore\Command\DisableCommand;
use Mine\AppStore\Command\DiscoverCommand;
use Mine\AppStore\Command\DoctorCommand;
use Mine\AppStore\Command\EnableCommand;

beforeEach(function () {
    $this->basePath = appstore_temp_path();
    $this->app = appstore_application($this->basePath);
});

afterEach(function () {
    appstore_delete_path($this->basePath);
});

test('discover command can render json and tables', function () {
    $service = new AppStoreFakeLifecycleService(discoverResult: [
        [
            'name' => 'acme/alpha',
            'version' => '1.0.0',
            'provider' => 'Plugin\\Acme\\Alpha\\PluginServiceProvider',
            'status' => 'Discovered',
            'errors' => [],
        ],
        [
            'name' => 'acme/bad',
            'version' => '1.0.0',
            'provider' => '',
            'status' => 'Invalid',
            'errors' => ['Provider is required.'],
        ],
    ]);

    $jsonTester = appstore_command_tester(new DiscoverCommand(), $this->app, $service);
    $jsonTester->execute(['--json' => true]);

    expect($jsonTester->getStatusCode())->toBe(0)
        ->and($jsonTester->getDisplay())->toContain('"name": "acme/alpha"');

    $tableTester = appstore_command_tester(new DiscoverCommand(), $this->app, $service);
    $tableTester->execute([]);

    expect($tableTester->getStatusCode())->toBe(0)
        ->and($tableTester->getDisplay())->toContain('acme/bad')
        ->and($tableTester->getDisplay())->toContain('Provider is required.');
});

test('enable and disable commands delegate to the lifecycle service', function () {
    $service = new AppStoreFakeLifecycleService();

    $enableTester = appstore_command_tester(new EnableCommand(), $this->app, $service);
    $enableTester->execute(['name' => 'acme/alpha']);

    $disableTester = appstore_command_tester(new DisableCommand(), $this->app, $service);
    $disableTester->execute(['name' => 'acme/alpha']);

    expect($enableTester->getStatusCode())->toBe(0)
        ->and($disableTester->getStatusCode())->toBe(0)
        ->and($service->enabled)->toBe(['acme/alpha'])
        ->and($service->disabled)->toBe(['acme/alpha'])
        ->and($enableTester->getDisplay())->toContain('enabled')
        ->and($disableTester->getDisplay())->toContain('disabled');
});

test('enable and disable commands render lifecycle failures', function () {
    $enableService = new AppStoreFakeLifecycleService(throwOnEnable: new RuntimeException('cannot enable'));
    $enableTester = appstore_command_tester(new EnableCommand(), $this->app, $enableService);
    $enableTester->execute(['name' => 'acme/alpha']);

    $disableService = new AppStoreFakeLifecycleService(throwOnDisable: new RuntimeException('cannot disable'));
    $disableTester = appstore_command_tester(new DisableCommand(), $this->app, $disableService);
    $disableTester->execute(['name' => 'acme/alpha']);

    expect($enableTester->getStatusCode())->toBe(1)
        ->and($enableTester->getDisplay())->toContain('cannot enable')
        ->and($disableTester->getStatusCode())->toBe(1)
        ->and($disableTester->getDisplay())->toContain('cannot disable');
});

test('cache and clear commands delegate to the lifecycle service', function () {
    $service = new AppStoreFakeLifecycleService(cacheResult: [
        ['name' => 'acme/alpha'],
        ['name' => 'acme/beta'],
    ]);

    $cacheTester = appstore_command_tester(new CacheCommand(), $this->app, $service);
    $cacheTester->execute([]);

    $clearTester = appstore_command_tester(new ClearCommand(), $this->app, $service);
    $clearTester->execute([]);

    expect($cacheTester->getStatusCode())->toBe(0)
        ->and($cacheTester->getDisplay())->toContain('Cached 2 enabled plugin')
        ->and($clearTester->getStatusCode())->toBe(0)
        ->and($service->cleared)->toBeTrue();
});

test('cache command renders lifecycle failures', function () {
    $service = new AppStoreFakeLifecycleService(throwOnCache: new RuntimeException('cache failed'));
    $tester = appstore_command_tester(new CacheCommand(), $this->app, $service);

    $tester->execute([]);

    expect($tester->getStatusCode())->toBe(1)
        ->and($tester->getDisplay())->toContain('cache failed');
});

test('doctor command renders all diagnostics or one plugin', function () {
    $service = new AppStoreFakeLifecycleService(doctorResult: [
        [
            'name' => 'acme/alpha',
            'status' => 'Enabled',
            'path' => '/tmp/alpha',
            'errors' => [],
        ],
        [
            'name' => 'acme/bad',
            'status' => 'Invalid',
            'path' => '/tmp/bad',
            'errors' => ['Provider is required.'],
        ],
    ]);

    $allTester = appstore_command_tester(new DoctorCommand(), $this->app, $service);
    $allTester->execute([]);

    $oneTester = appstore_command_tester(new DoctorCommand(), $this->app, $service);
    $oneTester->execute(['name' => 'acme/bad']);

    expect($allTester->getStatusCode())->toBe(0)
        ->and($allTester->getDisplay())->toContain('acme/alpha')
        ->and($oneTester->getStatusCode())->toBe(0)
        ->and($oneTester->getDisplay())->toContain('Provider is required.')
        ->and($oneTester->getDisplay())->not->toContain('acme/alpha');
});
