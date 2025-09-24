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
use Mine\Doctrine\ConfigProvider;
use Mine\Doctrine\ManagerRegistry;

describe('ConfigProvider', static function () {
    it('returns correct configuration structure', static function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config)->toBeArray();
        expect($config)->toHaveKeys(['annotations', 'dependencies']);
    });

    it('configures annotations scanning correctly', static function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config['annotations'])->toHaveKey('scan');
        expect($config['annotations']['scan'])->toHaveKey('paths');
        expect($config['annotations']['scan']['paths'])->toBeArray();
        expect($config['annotations']['scan']['paths'])->toContain(dirname(__DIR__, 3) . '/src/Doctrine/');
    });

    it('configures dependencies correctly', static function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        expect($config['dependencies'])->toBeArray();
        expect($config['dependencies'])->toHaveKey(Doctrine\Persistence\ManagerRegistry::class);
        expect($config['dependencies'][Doctrine\Persistence\ManagerRegistry::class])
            ->toBe(ManagerRegistry::class);
    });

    it('is invokable', static function () {
        $configProvider = new ConfigProvider();

        expect(is_callable($configProvider))->toBeTrue();
        expect($configProvider)->toBeInstanceOf(ConfigProvider::class);
    });

    it('returns immutable configuration', static function () {
        $configProvider = new ConfigProvider();
        $config1 = $configProvider();
        $config2 = $configProvider();

        expect($config1)->toEqual($config2);
    });

    it('includes correct scan path', static function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        $expectedPath = dirname(__DIR__, 3) . '/src/Doctrine/';
        $actualPaths = $config['annotations']['scan']['paths'];

        expect($actualPaths)->toContain($expectedPath);
    });

    it('has only required configuration keys', static function () {
        $configProvider = new ConfigProvider();
        $config = $configProvider();

        $expectedKeys = ['annotations', 'dependencies'];
        $actualKeys = array_keys($config);

        expect($actualKeys)->toEqual($expectedKeys);
    });
});
