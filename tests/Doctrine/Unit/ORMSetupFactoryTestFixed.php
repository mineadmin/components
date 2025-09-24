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
use Doctrine\ORM\Configuration;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Contract\ConfigInterface;
use Mine\Doctrine\Config;
use Mine\Doctrine\ORMSetupFactory;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

describe('ORMSetupFactory (Final Class Testing)', function () {
    beforeEach(function () {
        // Mock only interfaces, use real final class
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->cacheManager = Mockery::mock(CacheManager::class);

        // Create real instances
        $this->config = new Config($this->configInterface);
        $this->factory = new ORMSetupFactory($this->config, $this->cacheManager);
    });

    it('creates ORM configuration with default settings', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'paths' => ['/app/Entity'],
                'isDevMode' => true,
                'cache' => 'default',
                'proxy_dir' => '/runtime/container/doctrine/proxies',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('handles empty configuration with defaults', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([]); // Empty config

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default') // Should default to 'default'
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('propagates cache manager errors', function () {
        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'invalid_driver',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('invalid_driver')
            ->andThrow(new InvalidArgumentException('Cache driver not found'));

        expect(fn () => $this->factory->make())
            ->toThrow(InvalidArgumentException::class, 'Cache driver not found');
    });

    it('uses different cache drivers correctly', function () {
        $redisDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'redis',
                'isDevMode' => false,
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($redisDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
        expect($configuration->getMetadataCache())->not->toBeNull();
    });

    it('configures development mode correctly', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'isDevMode' => true,
                'cache' => 'memory',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('memory')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // In dev mode, auto-generate proxy classes should be enabled
        expect($configuration->getAutoGenerateProxyClasses())->toBeTrue();

        // Query cache should be null in dev mode
        expect($configuration->getQueryCache())->toBeNull();
    });

    it('configures production mode correctly', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'isDevMode' => false,
                'cache' => 'redis',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // In production mode, auto-generate should be disabled
        expect($configuration->getAutoGenerateProxyClasses())->toBeFalse();

        // All caches should be enabled
        expect($configuration->getQueryCache())->not->toBeNull();
        expect($configuration->getMetadataCache())->not->toBeNull();
        expect($configuration->getResultCache())->not->toBeNull();
    });

    it('handles custom entity paths', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $customPaths = ['/custom/path1', '/custom/path2'];

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'paths' => $customPaths,
                'cache' => 'default',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // Verify that metadata driver is configured
        expect($configuration->getMetadataDriverImpl())->not->toBeNull();
    });

    it('creates PSR16 cache adapter with correct prefix', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'test_cache',
            ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('test_cache')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
        expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);
    });
});
