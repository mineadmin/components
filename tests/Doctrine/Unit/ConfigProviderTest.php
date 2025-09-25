<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Doctrine\Persistence\ManagerRegistry as DoctrineManagerRegistry;
/*
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Mine\Doctrine\ConfigProvider;

describe('ConfigProvider', function () {
    it('returns correct configuration structure', function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config)->toBeArray();
        expect($config)->toHaveKeys(['annotations', 'dependencies']);
    });

    it('configures annotations scanning correctly', function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config['annotations'])->toHaveKey('scan');
        expect($config['annotations']['scan'])->toHaveKey('paths');
        expect($config['annotations']['scan']['paths'])->toBeArray();
        expect($config['annotations']['scan']['paths'])->toContain(dirname(__DIR__, 3) . '/src/Doctrine/');
    });

    it('configures dependencies correctly', function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config['dependencies'])->toBeArray();
        expect($config['dependencies'])->toHaveKey(DoctrineManagerRegistry::class);
    });

    it('is invokable', function () {
        $configProvider = new ConfigProvider();

        expect(is_callable($configProvider))->toBeTrue();
        expect($configProvider)->toBeInstanceOf(ConfigProvider::class);
    });

    it('returns immutable configuration', function () {
        $configProvider = new ConfigProvider();
        $config1 = $configProvider();
        $config2 = $configProvider();

        expect($config1)->toEqual($config2);
    });

    it('includes correct scan path', function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        $expectedPath = dirname(__DIR__, 3) . '/src/Doctrine/';
        $actualPaths = $config['annotations']['scan']['paths'];

        expect($actualPaths)->toContain($expectedPath);
    });

    it('has only required configuration keys', function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        $expectedKeys = ['annotations', 'dependencies', 'publish'];
        $actualKeys = array_keys($config);

        expect($actualKeys)->toEqual($expectedKeys);
    });
});
