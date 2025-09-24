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
use Doctrine\DBAL\Connection as DBALConnection;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\PoolOptionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Support\SafeCaller;
use Mine\Doctrine\Pool\Connection;
use Mine\Doctrine\Pool\ConnectionFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

describe('Connection', function () {
    beforeEach(function () {
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->pool = Mockery::mock(PoolInterface::class);
        $this->poolOption = Mockery::mock(PoolOptionInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        // Create real ConnectionFactory since it's final
        $safeCaller = new SafeCaller($this->container);
        $this->factory = new ConnectionFactory($safeCaller);
        $this->dbalConnection = Mockery::mock(DBALConnection::class);

        $this->config = ['driver' => 'pdo_sqlite', 'path' => ':memory:'];

        // Setup pool option mock for check() method
        $this->poolOption->shouldReceive('getMaxIdleTime')->andReturn(60.0);
        $this->pool->shouldReceive('getOption')->andReturn($this->poolOption);

        // Setup container mocks
        $this->container->shouldReceive('has')
            ->with(EventDispatcherInterface::class)
            ->andReturn(false);
        $this->container->shouldReceive('has')
            ->with(StdoutLoggerInterface::class)
            ->andReturn(false);
        $this->container->shouldReceive('get')
            ->with(ConnectionFactory::class)
            ->andReturn($this->factory);
        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->logger);

        $this->connection = new Connection($this->container, $this->pool, $this->config);
    });

    afterEach(static function () {
        Mockery::close();
    });

    it('creates connection instance', function () {
        expect($this->connection)->toBeInstanceOf(Connection::class);
    });

    it('reconnects successfully', function () {
        // Use real connection since ConnectionFactory is final
        $result = $this->connection->reconnect();
        expect($result)->toBeTrue();
    });

    it('closes non-existent connection', function () {
        expect($this->connection->close())->toBeTrue();
    });

    it('closes existing connection', function () {
        // Use real connection - it will create a SQLite in-memory connection
        $this->connection->reconnect();
        expect($this->connection->close())->toBeTrue();
    });

    it('gets active connection when check fails', function () {
        // Real connection will be created automatically
        $activeConnection = $this->connection->getActiveConnection();
        expect($activeConnection)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles multiple reconnections', function () {
        // Test multiple reconnections with real SQLite connection
        expect($this->connection->reconnect())->toBeTrue();
        expect($this->connection->reconnect())->toBeTrue();
    });

    it('throws exception on failed reconnection', function () {
        // Create config that will fail to connect - invalid driver
        $badConfig = ['driver' => 'invalid_driver_that_does_not_exist'];

        $badConnection = new Connection($this->container, $this->pool, $badConfig);

        expect(static fn () => $badConnection->getActiveConnection())
            ->toThrow(ConnectionException::class, 'Connection reconnect failed.');
    });

    it('delegates method calls to DBAL connection', function () {
        // Ensure we have an active connection
        $activeConnection = $this->connection->getActiveConnection();
        expect($activeConnection)->toBeInstanceOf(DBALConnection::class);

        // Test that method delegation works - just verify the method was called
        // Check if autocommit is initially true
        $autoCommit = $this->connection->isAutoCommit();
        expect($autoCommit)->toBeTrue();

        // Test a method that doesn't require transaction state
        $dbPlatform = $this->connection->getDatabasePlatform();
        expect($dbPlatform)->not->toBeNull();
    });

    it('maintains connection lifecycle', function () {
        // Test connection lifecycle with real connections
        $conn1 = $this->connection->getActiveConnection();
        expect($conn1)->toBeInstanceOf(DBALConnection::class);

        // Verify connection is active
        expect($this->connection->getDatabasePlatform())->not->toBeNull();

        // Close and manually reconnect
        $this->connection->close();
        expect($this->connection->reconnect())->toBeTrue();

        $conn2 = $this->connection->getActiveConnection();
        expect($conn2)->toBeInstanceOf(DBALConnection::class);

        // They should be different instances since we closed and reconnected
        expect($conn1)->not->toBe($conn2);
    });
});
