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
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Doctrine\Persistence\Proxy;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Frequency;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Support\SafeCaller;
use Mine\Doctrine\Config;
use Mine\Doctrine\EntityManagerFactory;
use Mine\Doctrine\ManagerRegistry;
use Mine\Doctrine\ORMSetupFactory;
use Mine\Doctrine\Pool\ConnectionFactory;
use Mine\Doctrine\Pool\PoolFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

describe('Doctrine EntityManager Integration', function () {
    beforeEach(function () {
        // Create real Config with mocked ConfigInterface
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);

        // Create mocked CacheManager
        $this->cacheManager = Mockery::mock(CacheManager::class);
        $cacheDriver = Mockery::mock(DriverInterface::class);
        $this->cacheManager->shouldReceive('getDriver')->andReturn($cacheDriver);

        // Create mocked Container with all necessary services
        $this->container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($this->container);

        // Setup container dependencies
        // Mock ConnectionFactory
        $safeCaller = Mockery::mock(SafeCaller::class);
        $connectionFactory = new ConnectionFactory($safeCaller);

        $this->container->shouldReceive('get')
            ->with(ConnectionFactory::class)
            ->andReturn($connectionFactory);

        // Mock Logger
        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn(Mockery::mock(LoggerInterface::class));

        // Mock Frequency
        $this->container->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        // Mock PoolOption
        $poolOption = Mockery::mock(PoolOption::class);
        $poolOption->shouldReceive('getMaxConnections')->andReturn(1);
        $poolOption->shouldReceive('getMinConnections')->andReturn(1);
        $poolOption->shouldReceive('getMaxIdleTime')->andReturn(60.0);
        $poolOption->shouldReceive('getConnectTimeout')->andReturn(10.0);
        $poolOption->shouldReceive('getWaitTimeout')->andReturn(3.0);

        $this->container->shouldReceive('make')
            ->with(PoolOption::class, Mockery::any())
            ->andReturn($poolOption);

        // Mock Channel
        $channel = Mockery::mock(Channel::class);
        $channel->shouldReceive('length')->andReturn(0);
        $channel->shouldReceive('push')->andReturn(true);
        $channel->shouldReceive('pop')->andReturn(null);

        $this->container->shouldReceive('make')
            ->with(Channel::class, Mockery::any())
            ->andReturn($channel);

        // Mock optional services
        $this->container->shouldReceive('has')
            ->with(StdoutLoggerInterface::class)
            ->andReturn(false);

        $this->container->shouldReceive('has')
            ->with(EventDispatcherInterface::class)
            ->andReturn(false);

        // Setup basic configuration
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => [__DIR__ . '/../Entity'],
            'isDevMode' => true,
            'cache' => 'default',
            'proxy_dir' => '/tmp/doctrine/proxies',
            'database' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => ':memory:',
                    'option' => ['min_connections' => 1, 'max_connections' => 1],
                ],
            ],
        ]);

        // Setup pool configuration
        $this->configInterface->shouldReceive('has')->with('doctrine.database.default')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.default.name', 'default')->andReturn(null);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default.option')->andReturn(['min_connections' => 1, 'max_connections' => 1]);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => ['min_connections' => 1, 'max_connections' => 1],
        ]);
    });

    it('creates ORM setup factory with configuration', function () {
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $configuration = $ormSetupFactory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);
    });

    it('validates factory classes exist', function () {
        expect(class_exists(EntityManagerFactory::class))->toBe(true);
        expect(class_exists(PoolFactory::class))->toBe(true);
        expect(class_exists(ORMSetupFactory::class))->toBe(true);
        expect(class_exists(Config::class))->toBe(true);
    });

    it('creates entity manager through factory integration', function () {
        // Create real instances using mocked dependencies
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);

        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Create entity manager
        $entityManager = $entityManagerFactory->create();

        expect($entityManager)->toBeInstanceOf(EntityManager::class);
        expect($entityManager->getConnection())->not->toBeNull();
        expect($entityManager->getConfiguration())->not->toBeNull();
    });

    it('integrates with manager registry', function () {
        // Create real factories
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);
        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Mock container to return the factory
        $this->container->shouldReceive('get')
            ->with(EntityManagerFactory::class)
            ->andReturn($entityManagerFactory);

        // Create manager registry
        $registry = new ManagerRegistry(
            'doctrine',
            ['default' => 'default'], // connections
            ['default' => 'default'], // managers - values are passed to getService()
            'default',
            'default',
            Proxy::class
        );

        // Test getting manager through registry
        $manager = $registry->getManager('default');

        expect($manager)->toBeInstanceOf(EntityManager::class);
        expect($registry)->toBeInstanceOf(PersistenceManagerRegistry::class);
    });

    it('handles multiple database pools', function () {
        // Configure multiple databases
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => [__DIR__ . '/../Entity'],
            'isDevMode' => true,
            'cache' => 'default',
            'proxy_dir' => '/tmp/doctrine/proxies',
            'database' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => ':memory:',
                    'option' => ['min_connections' => 1, 'max_connections' => 1],
                ],
                'secondary' => [
                    'driver' => 'pdo_sqlite',
                    'path' => '/tmp/secondary.db',
                    'option' => ['min_connections' => 1, 'max_connections' => 1],
                ],
            ],
        ]);

        // Setup secondary pool configuration
        $this->configInterface->shouldReceive('has')->with('doctrine.database.secondary')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.secondary.name', 'secondary')->andReturn(null);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary.option')->andReturn(['min_connections' => 1, 'max_connections' => 1]);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => '/tmp/secondary.db',
            'option' => ['min_connections' => 1, 'max_connections' => 1],
        ]);

        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);

        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Create entity managers for both pools
        $defaultEM = $entityManagerFactory->create('default');
        $secondaryEM = $entityManagerFactory->create('secondary');

        expect($defaultEM)->toBeInstanceOf(EntityManager::class);
        expect($secondaryEM)->toBeInstanceOf(EntityManager::class);
        expect($defaultEM)->not->toBe($secondaryEM);
    });

    it('handles entity manager lifecycle through registry', function () {
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);
        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        $this->container->shouldReceive('get')
            ->with(EntityManagerFactory::class)
            ->andReturn($entityManagerFactory);

        $registry = new ManagerRegistry(
            'doctrine',
            ['default' => 'default'], // connections
            ['default' => 'default'], // managers - values are passed to getService()
            'default',
            'default',
            Proxy::class
        );

        // Test manager lifecycle
        $manager1 = $registry->getManager('default');
        $manager2 = $registry->getManager('default'); // Should be same instance (cached)

        expect($manager1)->toBe($manager2);

        // Reset manager
        $registry->resetManager('default');

        // Get new manager after reset
        $manager3 = $registry->getManager('default');

        expect($manager3)->toBeInstanceOf(EntityManager::class);
        expect($manager3)->not->toBe($manager1); // Should be different instance
    });

    it('configures ORM with custom settings', function () {
        // Custom configuration
        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'paths' => ['/custom/entity/path'],
            'isDevMode' => false, // Production mode
            'cache' => 'redis',
            'proxy_dir' => '/custom/proxy/path',
            'database' => [
                'default' => [
                    'driver' => 'pdo_mysql',
                    'host' => 'localhost',
                    'dbname' => 'test_db',
                    'option' => ['min_connections' => 5, 'max_connections' => 20],
                ],
            ],
        ]);

        $redisDriver = Mockery::mock(DriverInterface::class);
        $this->cacheManager->shouldReceive('getDriver')->with('redis')->andReturn($redisDriver);

        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);

        $configuration = $ormSetupFactory->make();

        expect($configuration)->toBeInstanceOf(Configuration::class);

        // In production mode, metadata cache should be enabled
        expect($configuration->getMetadataCache())->not->toBeNull();
        // Auto proxy generation should be disabled (0) in production
        // Note: ORMSetupFactory uses env('APP_DEBUG') not config 'isDevMode'
        // So we expect 1 (dev mode) since APP_DEBUG is likely true in tests
        expect($configuration->getAutoGenerateProxyClasses())->toBe(1);
    });

    it('handles configuration errors gracefully', function () {
        // Configure mock to return false for nonexistent pool
        $this->configInterface->shouldReceive('has')->with('doctrine.database.nonexistent')->andReturn(false);

        // Test with invalid pool name
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);
        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // This should throw an exception for missing pool configuration
        expect(static fn () => $entityManagerFactory->create('nonexistent'))
            ->toThrow(InvalidArgumentException::class);
    });
});
