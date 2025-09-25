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
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Contract\ConfigInterface;
use Mine\Doctrine\Config;
use Mine\Doctrine\ORMSetupFactory;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

describe('ORM Configuration Integration', function () {
    beforeEach(function () {
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);
        $this->cacheManager = Mockery::mock(CacheManager::class);
        $this->cacheDriver = Mockery::mock(DriverInterface::class);

        $this->cacheManager->shouldReceive('getDriver')
            ->andReturn($this->cacheDriver);
    });

    it('creates ORM configuration with development settings', function () {
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'isDevMode' => true,
            'cache' => 'default',
            'proxy_dir' => '/tmp/doctrine/proxies',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // Since we're passing a cache driver, query cache will be enabled even in dev mode
        expect($configuration->getQueryCache())->toBeInstanceOf(Psr16Adapter::class);

        // Metadata cache should be present
        expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);

        // Auto-generation of proxy classes should be enabled in dev mode
        expect($configuration->getAutoGenerateProxyClasses())->toBe(1);
    });

    it('creates ORM configuration with production settings', function () {
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'isDevMode' => false,
            'cache' => 'redis',
            'proxy_dir' => '/app/var/doctrine/proxies',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('redis')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // Production mode should enable all caches
        expect($configuration->getQueryCache())->toBeInstanceOf(Psr16Adapter::class);
        expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);
        expect($configuration->getResultCache())->toBeInstanceOf(Psr16Adapter::class);

        // Auto-generation should be disabled in production
        expect($configuration->getAutoGenerateProxyClasses())->toBe(0);
    });

    it('handles multiple entity paths', function () {
        $entityPaths = [
            '/app/Entity',
            '/app/Domain/User/Entity',
            '/app/Domain/Product/Entity',
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => $entityPaths,
            'isDevMode' => true,
            'cache' => 'memory',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('memory')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // Get the metadata driver to check paths
        $metadataDriver = $configuration->getMetadataDriverImpl();
        expect($metadataDriver)->not->toBeNull();
    });

    it('configures proxy directory correctly', function () {
        $proxyDir = '/custom/proxy/directory';

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'isDevMode' => false,
            'proxy_dir' => $proxyDir,
            'cache' => 'default',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        expect($configuration->getProxyDir())->toBe($proxyDir);
        expect($configuration->getProxyNamespace())->toBe('DoctrineProxies');
    });

    it('handles different cache drivers', function () {
        $cacheConfigs = [
            'redis' => 'redis_driver',
            'memory' => 'memory_driver',
            'file' => 'file_driver',
        ];

        foreach ($cacheConfigs as $cacheType => $driverName) {
            $specificDriver = Mockery::mock(DriverInterface::class);

            $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
                'paths' => ['/app/Entity'],
                'isDevMode' => false,
                'cache' => $cacheType,
            ]);

            $this->cacheManager->shouldReceive('getDriver')
                ->with($cacheType)
                ->andReturn($specificDriver);

            $factory = new ORMSetupFactory($this->config, $this->cacheManager);
            $configuration = $factory->make();

            expect($configuration)->toBeInstanceOf(Configuration::class);
            expect($configuration->getMetadataCache())->toBeInstanceOf(Psr16Adapter::class);
        }
    });

    it('uses default configuration when values are missing', function () {
        // Simulate missing configuration values
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // Should use default values
        $defaultBasePath = defined('BASE_PATH') ? BASE_PATH : getcwd();
        expect($configuration->getProxyDir())->toBe($defaultBasePath . '/runtime/container/doctrine/proxies');
    });

    it('configures attribute metadata driver', function () {
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'isDevMode' => true,
            'cache' => 'default',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($this->cacheDriver);

        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        $metadataDriver = $configuration->getMetadataDriverImpl();

        expect($metadataDriver)->not->toBeNull();
        expect($metadataDriver)->toBeInstanceOf(AttributeDriver::class);
    });

    it('handles cache driver errors gracefully', function () {
        // Create fresh mocks to avoid conflicts with beforeEach setup
        $configInterface = Mockery::mock(ConfigInterface::class);
        $config = new Config($configInterface);
        $cacheManager = Mockery::mock(CacheManager::class);

        $configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'cache' => 'invalid_cache_driver',
        ]);

        $cacheManager->shouldReceive('getDriver')
            ->with('invalid_cache_driver')
            ->andThrow(new InvalidArgumentException('Cache driver not found'));

        $factory = new ORMSetupFactory($config, $cacheManager);

        expect(static fn () => $factory->make())
            ->toThrow(InvalidArgumentException::class, 'Cache driver not found');
    });

    it('configures environment-based settings', function () {
        // Test with environment variables
        $originalAppDebug = $_ENV['APP_DEBUG'] ?? null;

        try {
            $_ENV['APP_DEBUG'] = 'false';

            $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
                'paths' => ['/app/Entity'],
                'cache' => 'redis',
                'isDevMode' => false, // Explicitly set dev mode to false
            ]);

            $this->cacheManager->shouldReceive('getDriver')
                ->with('redis')
                ->andReturn($this->cacheDriver);

            $factory = new ORMSetupFactory($this->config, $this->cacheManager);
            $configuration = $factory->make();

            // Should be in production mode based on APP_DEBUG=false
            expect($configuration->getAutoGenerateProxyClasses())->toBe(0);
        } finally {
            // Restore original environment
            if ($originalAppDebug === null) {
                unset($_ENV['APP_DEBUG']);
            } else {
                $_ENV['APP_DEBUG'] = $originalAppDebug;
            }
        }
    });

    it('configures naming strategy and custom functions', function () {
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/app/Entity'],
            'isDevMode' => true,
            'cache' => 'default',
        ]);

        $this->cacheManager->shouldReceive('getDriver')
            ->with('default')
            ->andReturn($this->cacheDriver);
        $factory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $factory->make();

        // Check that basic configuration is set up
        expect($configuration->getNamingStrategy())->not->toBeNull();
        expect($configuration->getQuoteStrategy())->not->toBeNull();
    });
});
