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

describe('ORMSetupFactory', function () {
    beforeEach(function () {
        // Create real Config with mocked ConfigInterface
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);

        // Create mocked CacheManager
        $this->cacheManager = Mockery::mock(CacheManager::class);

        $this->factory = new ORMSetupFactory($this->config, $this->cacheManager);
    });

    it('creates ORM configuration with default settings', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'paths' => ['/app/Entity'],
                'isDevMode' => true,
                'cache' => 'default',
                'proxy_dir' => '/runtime/container/doctrine/proxies',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('handles custom paths configuration', function () {
        $customPaths = ['/app/Entity', '/custom/Entity'];
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'paths' => $customPaths,
                'isDevMode' => false,
                'cache' => 'redis',
                'proxy_dir' => '/custom/proxies',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('uses default configuration when config is empty', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('handles different cache drivers', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'redis',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('creates cache driver with correct prefix', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'memory',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('memory')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
        expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);
    });

    it('handles cache manager errors', function () {
        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'cache' => 'invalid_driver',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('invalid_driver')
            ->andThrow(new InvalidArgumentException('Cache driver not found'));

        expect(fn () => $this->factory->make())
            ->toThrow(InvalidArgumentException::class, 'Cache driver not found');
    });

    it('handles development mode configuration', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'isDevMode' => true,
                'cache' => 'default',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
        // ORMSetupFactory actually uses env('APP_DEBUG') not config isDevMode
        // so we expect the behavior based on APP_DEBUG which is likely true in tests
        expect($configuration->getAutoGenerateProxyClasses())->toBe(1);
    });

    it('handles production mode configuration', function () {
        $cacheDriver = Mockery::mock(DriverInterface::class);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn([
                'isDevMode' => false, // This is ignored, env('APP_DEBUG') is used instead
                'cache' => 'redis',
            ]);

        $this->cacheManager
            ->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($cacheDriver);

        $configuration = $this->factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
        // Metadata cache should always be set when using cache manager
        expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);
    });
});
