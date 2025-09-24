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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

describe('Pool', function () {
    beforeEach(function () {
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);
        $this->poolName = 'test_pool';
    });

    it('creates pool with valid configuration', function () {
        $databaseConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'option' => [
                'min_connections' => 1,
                'max_connections' => 10,
            ],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.test_pool')
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with('doctrine.database.test_pool.name', 'test_pool')
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool.option')
            ->andReturn($databaseConfig['option']);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool')
            ->andReturn($databaseConfig);

        // Mock Frequency creation
        $this->container
            ->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        $pool = new Pool($this->container, $this->config, $this->poolName);

        expect($pool->getName())->toBe('test_pool');
    });

    it('throws exception when pool configuration not found', function () {
        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.test_pool')
            ->andReturn(false);

        expect(fn () => new Pool($this->container, $this->config, $this->poolName))
            ->toThrow(InvalidArgumentException::class, 'Database pool [test_pool] not found');
    });

    it('creates connection with correct configuration', function () {
        $databaseConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'option' => [],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.test_pool')
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with('doctrine.database.test_pool.name', 'test_pool')
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool.option')
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool')
            ->andReturn($databaseConfig);

        // Mock optional container services for Connection constructor
        $this->container->shouldReceive('has')
            ->with(EventDispatcherInterface::class)
            ->andReturn(false);
        $this->container->shouldReceive('has')
            ->with(StdoutLoggerInterface::class)
            ->andReturn(false);

        // Mock required services for Connection constructor
        $safeCaller = Mockery::mock(SafeCaller::class);
        $connectionFactory = new ConnectionFactory($safeCaller);
        $this->container->shouldReceive('get')
            ->with(ConnectionFactory::class)
            ->andReturn($connectionFactory);
        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn(Mockery::mock(LoggerInterface::class));

        $this->container
            ->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        $pool = new Pool($this->container, $this->config, $this->poolName);

        // Test createConnection method via reflection since it's protected
        $reflection = new ReflectionClass($pool);
        $createConnectionMethod = $reflection->getMethod('createConnection');
        $createConnectionMethod->setAccessible(true);

        $connection = $createConnectionMethod->invoke($pool);

        expect($connection)->toBeInstanceOf(Connection::class);
    });

    it('handles empty configuration options', function () {
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.test_pool')
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with('doctrine.database.test_pool.name', 'test_pool')
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool.option')
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool')
            ->andReturn($databaseConfig);

        $this->container
            ->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        $pool = new Pool($this->container, $this->config, $this->poolName);

        expect($pool)->toBeInstanceOf(Pool::class);
        expect($pool->getName())->toBe('test_pool');
    });

    it('sets pool name in configuration', function () {
        $databaseConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'option' => [],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.custom_name')
            ->andReturn(true);

        // Verify that the pool name is set in config
        $this->configInterface
            ->shouldReceive('set')
            ->with('doctrine.database.custom_name.name', 'custom_name')
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.custom_name.option')
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.custom_name')
            ->andReturn($databaseConfig);

        $this->container
            ->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        new Pool($this->container, $this->config, 'custom_name');
    });

    it('initializes frequency object correctly', function () {
        $databaseConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'option' => [],
        ];

        $frequency = Mockery::mock(Frequency::class);

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.test_pool')
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with('doctrine.database.test_pool.name', 'test_pool')
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool.option')
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with('doctrine.database.test_pool')
            ->andReturn($databaseConfig);

        $this->container
            ->shouldReceive('make')
            ->with(Frequency::class, Mockery::type('array'))
            ->andReturn($frequency);

        $pool = new Pool($this->container, $this->config, $this->poolName);

        // Use reflection to check that frequency is set
        $reflection = new ReflectionClass($pool);
        $frequencyProperty = $reflection->getProperty('frequency');
        $frequencyProperty->setAccessible(true);

        expect($frequencyProperty->getValue($pool))->toBeInstanceOf(Frequency::class);
    });
});
