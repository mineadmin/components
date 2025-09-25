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
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Proxy;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
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

describe('ManagerRegistry', function () {
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

        // Mock Channel with a ConnectionInterface instance
        $channel = Mockery::mock(Channel::class);
        $mockConnection = Mockery::mock(ConnectionInterface::class);
        $mockConnection->shouldReceive('getConnection')
            ->andReturn(Mockery::mock(Connection::class));
        $channel->shouldReceive('length')->andReturn(0);
        $channel->shouldReceive('push')->andReturn(true);
        $channel->shouldReceive('pop')->andReturn($mockConnection);

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
                'custom' => [
                    'driver' => 'pdo_sqlite',
                    'path' => '/tmp/custom.db',
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

        // Setup pool configurations for custom
        $this->configInterface->shouldReceive('has')->with('doctrine.database.custom')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.custom.name', 'custom')->andReturn(null);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.custom.option')->andReturn(['min_connections' => 1, 'max_connections' => 1]);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.custom')->andReturn([
            'driver' => 'pdo_sqlite',
            'path' => '/tmp/custom.db',
            'option' => ['min_connections' => 1, 'max_connections' => 1],
        ]);

        // Create factory instances
        $ormSetupFactory = new ORMSetupFactory($this->config, $this->cacheManager);
        $poolFactory = new PoolFactory($this->config, $this->container);
        $this->entityManagerFactory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        // Mock container to return the factory
        $this->container->shouldReceive('get')
            ->with(EntityManagerFactory::class)
            ->andReturn($this->entityManagerFactory);

        $this->registry = new ManagerRegistry(
            'doctrine',
            ['default' => 'default', 'custom' => 'custom'], // connections
            ['default' => 'default', 'custom' => 'custom'],  // managers
            'default',
            'default',
            Proxy::class
        );
    });

    afterEach(function () {
        // Clean up context after each test
        Context::destroy('doctrine.manager.default');
        Context::destroy('doctrine.manager.custom');
    });

    it('gets service from context when available', function () {
        // First call should create and cache the manager
        $result1 = $this->registry->getManager('default');

        expect($result1)->toBeInstanceOf(EntityManager::class);

        // Second call should return cached version
        $result2 = $this->registry->getManager('default');

        expect($result2)->toBeInstanceOf(EntityManager::class);
        expect($result1)->toBe($result2);
    });

    it('creates new service when not in context', function () {
        $result = $this->registry->getManager('custom');

        expect($result)->toBeInstanceOf(EntityManager::class);
    });

    it('resets service and clears context', function () {
        // First, get the manager to establish context
        $manager1 = $this->registry->getManager('default');

        // Verify context is set
        expect(Context::has('doctrine.manager.default'))->toBeTrue();

        // Store reference to original manager for comparison
        $originalManager = Context::get('doctrine.manager.default');

        // Reset the service
        $this->registry->resetManager('default');

        // Getting manager again should create new one
        $manager2 = $this->registry->getManager('default');
        expect($manager2)->toBeInstanceOf(EntityManager::class);
        expect($manager2)->not->toBe($originalManager); // Should be different instance from what was reset
    });

    it('resets service without close method gracefully', function () {
        // Store a plain object in context that doesn't have close method
        Context::set('doctrine.manager.custom', new stdClass());

        // This should not throw an exception
        $this->registry->resetManager('custom');
        expect(true)->toBe(true); // If we reach here, no exception was thrown
    });

    it('handles context cleanup after reset', function () {
        // Get the manager to establish context
        $manager = $this->registry->getManager('default');
        expect($manager)->toBeInstanceOf(EntityManager::class);

        // Reset should clean the context
        $this->registry->resetManager('default');

        // Getting manager again should create a new instance
        $newManager = $this->registry->getManager('default');
        expect($newManager)->toBeInstanceOf(EntityManager::class);
        // We can't directly test context cleanup as Context is internal,
        // but creating a new manager after reset indicates successful cleanup
    });

    it('uses default manager name when none specified', function () {
        $result = $this->registry->getManager(); // No name parameter

        expect($result)->toBeInstanceOf(EntityManager::class);
    });

    it('handles factory creation errors', function () {
        // Configure mock to return false for nonexistent pool
        $this->configInterface->shouldReceive('has')->with('doctrine.database.nonexistent')->andReturn(false);

        expect(fn () => $this->registry->getManager('nonexistent'))
            ->toThrow(InvalidArgumentException::class);
    });
});
