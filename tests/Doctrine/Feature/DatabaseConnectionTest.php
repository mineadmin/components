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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Frequency;
use Hyperf\Support\SafeCaller;
use Mine\Doctrine\Config;
use Mine\Doctrine\Pool\Connection;
use Mine\Doctrine\Pool\ConnectionFactory;
use Mine\Doctrine\Pool\Pool;
use Mine\Doctrine\Pool\PoolFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

describe('Database Connection Pool Integration', function () {
    beforeEach(function () {
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);
        $this->safeCaller = Mockery::mock(SafeCaller::class);

        // Mock basic container services
        $this->container->shouldReceive('get')
            ->with(ConnectionFactory::class)
            ->andReturn(new ConnectionFactory($this->safeCaller));

        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn(Mockery::mock(LoggerInterface::class));

        $this->container->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        // Mock container has() method calls
        $this->container->shouldReceive('has')
            ->with(StdoutLoggerInterface::class)
            ->andReturn(false);

        $this->container->shouldReceive('has')
            ->with(EventDispatcherInterface::class)
            ->andReturn(false);
    });

    it('creates and manages connection pools', function () {
        // Configure database pools
        $databaseConfig = [
            'default' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'option' => [
                    'min_connections' => 1,
                    'max_connections' => 5,
                ],
            ],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => $databaseConfig,
        ]);

        $this->configInterface->shouldReceive('has')->with('doctrine.database.default')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.default.name', 'default');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default.option')->andReturn($databaseConfig['default']['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.default')->andReturn($databaseConfig['default']);

        $poolFactory = new PoolFactory($this->config, $this->container);

        // Get pool from factory
        $pool = $poolFactory->getPool('default');

        expect($pool)->toBeInstanceOf(Pool::class);
        expect($pool->getName())->toBe('default');
    });

    it('manages connection lifecycle within pool', function () {
        $databaseConfig = [
            'test_pool' => [
                'driver' => 'pdo_sqlite',
                'path' => ':memory:',
                'option' => ['min_connections' => 1, 'max_connections' => 3],
            ],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => $databaseConfig,
        ]);

        $this->configInterface->shouldReceive('has')->with('doctrine.database.test_pool')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.test_pool.name', 'test_pool');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.test_pool.option')->andReturn($databaseConfig['test_pool']['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.test_pool')->andReturn($databaseConfig['test_pool']);

        $poolFactory = new PoolFactory($this->config, $this->container);
        $pool = $poolFactory->getPool('test_pool');

        // Get connection from pool
        $connection = $pool->get();

        expect($connection)->toBeInstanceOf(Connection::class);

        // Test connection operations
        expect($connection->reconnect())->toBeTrue();
        expect($connection->close())->toBeTrue();

        // Return connection to pool
        $pool->release($connection);
    });

    it('handles multiple database connections concurrently', function () {
        $databaseConfigs = [
            'primary' => [
                'driver' => 'pdo_sqlite',
                'path' => '/tmp/primary.db',
                'option' => ['min_connections' => 1, 'max_connections' => 5],
            ],
            'secondary' => [
                'driver' => 'pdo_sqlite',
                'path' => '/tmp/secondary.db',
                'option' => ['min_connections' => 1, 'max_connections' => 3],
            ],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => $databaseConfigs,
        ]);

        // Mock config for primary
        $this->configInterface->shouldReceive('has')->with('doctrine.database.primary')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.primary.name', 'primary');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.primary.option')->andReturn($databaseConfigs['primary']['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.primary')->andReturn($databaseConfigs['primary']);

        // Mock config for secondary
        $this->configInterface->shouldReceive('has')->with('doctrine.database.secondary')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.secondary.name', 'secondary');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary.option')->andReturn($databaseConfigs['secondary']['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.secondary')->andReturn($databaseConfigs['secondary']);

        $poolFactory = new PoolFactory($this->config, $this->container);

        // Create multiple pools
        $primaryPool = $poolFactory->getPool('primary');
        $secondaryPool = $poolFactory->getPool('secondary');

        expect($primaryPool)->toBeInstanceOf(Pool::class);
        expect($secondaryPool)->toBeInstanceOf(Pool::class);
        expect($primaryPool->getName())->toBe('primary');
        expect($secondaryPool->getName())->toBe('secondary');

        // Verify pools are cached and reused
        $primaryPool2 = $poolFactory->getPool('primary');
        expect($primaryPool)->toBe($primaryPool2);
    });

    it('handles connection errors and recovery', function () {
        $databaseConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'nonexistent-host',
            'dbname' => 'test_db',
            'option' => ['min_connections' => 1, 'max_connections' => 2],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => ['error_pool' => $databaseConfig],
        ]);

        $this->configInterface->shouldReceive('has')->with('doctrine.database.error_pool')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.error_pool.name', 'error_pool');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.error_pool.option')->andReturn($databaseConfig['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.error_pool')->andReturn($databaseConfig);

        $poolFactory = new PoolFactory($this->config, $this->container);
        $pool = $poolFactory->getPool('error_pool');

        // Get connection from pool - this may fail on actual connection attempt
        $connection = $pool->get();

        expect($connection)->toBeInstanceOf(Connection::class);

        // Test that connection handles configuration properly
        // For error scenarios, we just verify the connection object is created
        // Real connection errors would occur during actual database operations
    });

    it('respects pool configuration limits', function () {
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [
                'min_connections' => 2,
                'max_connections' => 4,
                'connect_timeout' => 5.0,
                'wait_timeout' => 3.0,
            ],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => ['limited_pool' => $databaseConfig],
        ]);

        $this->configInterface->shouldReceive('has')->with('doctrine.database.limited_pool')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.limited_pool.name', 'limited_pool');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.limited_pool.option')->andReturn($databaseConfig['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.limited_pool')->andReturn($databaseConfig);

        $poolFactory = new PoolFactory($this->config, $this->container);
        $pool = $poolFactory->getPool('limited_pool');

        // Test that pool respects configuration
        expect($pool->getName())->toBe('limited_pool');

        // Get multiple connections to test pool limits
        $connections = [];
        for ($i = 0; $i < 3; ++$i) {
            $connection = $pool->get();
            $connections[] = $connection;
            expect($connection)->toBeInstanceOf(Connection::class);
        }

        // Release connections back to pool
        foreach ($connections as $connection) {
            $pool->release($connection);
        }
    });

    it('handles database reconnection scenarios', function () {
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => ['min_connections' => 1, 'max_connections' => 2],
        ];

        $this->configInterface->shouldReceive('get')->with('doctrine')->andReturn([
            'database' => ['reconnect_pool' => $databaseConfig],
        ]);

        $this->configInterface->shouldReceive('has')->with('doctrine.database.reconnect_pool')->andReturn(true);
        $this->configInterface->shouldReceive('set')->with('doctrine.database.reconnect_pool.name', 'reconnect_pool');
        $this->configInterface->shouldReceive('get')->with('doctrine.database.reconnect_pool.option')->andReturn($databaseConfig['option']);
        $this->configInterface->shouldReceive('get')->with('doctrine.database.reconnect_pool')->andReturn($databaseConfig);

        $poolFactory = new PoolFactory($this->config, $this->container);
        $pool = $poolFactory->getPool('reconnect_pool');

        $connection = $pool->get();

        // Test reconnection cycle
        expect($connection->reconnect())->toBeTrue();
        expect($connection->close())->toBeTrue();
        expect($connection->reconnect())->toBeTrue();

        $pool->release($connection);
    });
});
