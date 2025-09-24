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
use Mine\Doctrine\ORMSetupFactory;
use Mine\Doctrine\Pool\ConnectionFactory;
use Mine\Doctrine\Pool\PoolFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

describe('EntityManagerFactory', function () {
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
                'secondary' => [
                    'driver' => 'pdo_sqlite',
                    'path' => '/tmp/secondary.db',
                    'option' => ['min_connections' => 1, 'max_connections' => 1],
                ],
            ],
        ]);

        // Setup pool configurations for default
        $this->configInterface->shouldReceive('has')->with('doctrine.database.default')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.default.name', 'default')->andReturn(null);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default.option')->andReturn(['min_connections' => 1, 'max_connections' => 1]);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => ['min_connections' => 1, 'max_connections' => 1],
        ]);

        // Setup pool configurations for secondary
        $this->configInterface->shouldReceive('has')->with('doctrine.database.secondary')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.secondary.name', 'secondary')->andReturn(null);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary.option')->andReturn(['min_connections' => 1, 'max_connections' => 1]);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => '/tmp/secondary.db',
            'option' => ['min_connections' => 1, 'max_connections' => 1],
        ]);
    });

    it('validates factory class exists', static function () {
        expect(class_exists(EntityManagerFactory::class))->toBe(true);
        expect(method_exists(EntityManagerFactory::class, 'create'))->toBe(true);
    });

    it('creates entity manager with default pool', function () {
        // Create real instances using mocked dependencies
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);

        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Create entity manager with default pool
        $entityManager = $entityManagerFactory->create();

        expect($entityManager)->toBeInstanceOf(EntityManager::class);
        expect($entityManager->getConnection())->not->toBeNull();
        expect($entityManager->getConfiguration())->not->toBeNull();
    });

    it('creates entity manager with custom pool name', function () {
        // Create real instances using mocked dependencies
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);

        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Create entity manager with secondary pool
        $entityManager = $entityManagerFactory->create('secondary');

        expect($entityManager)->toBeInstanceOf(EntityManager::class);
        expect($entityManager->getConnection())->not->toBeNull();
        expect($entityManager->getConfiguration())->not->toBeNull();
    });

    it('handles factory errors gracefully', function () {
        // Configure mock to return false for nonexistent pool
        $this->configInterface->shouldReceive('has')->with('doctrine.database.nonexistent')->andReturn(false);

        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);
        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // This should throw an exception for missing pool configuration
        expect(static fn () => $entityManagerFactory->create('nonexistent'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('uses orm setup factory for configuration', function () {
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);

        $entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Create entity manager and verify it uses the ORM configuration
        $entityManager = $entityManagerFactory->create();

        expect($entityManager)->toBeInstanceOf(EntityManager::class);

        // Verify that the configuration is properly set
        $configuration = $entityManager->getConfiguration();
        expect($configuration)->not->toBeNull();
        expect($configuration)->toBeInstanceOf(Configuration::class);

        // In dev mode (isDevMode=true), auto generate proxy classes should be enabled (1)
        expect($configuration->getAutoGenerateProxyClasses())->toBe(1);
    });
});
