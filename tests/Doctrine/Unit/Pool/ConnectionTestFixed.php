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
use Hyperf\Contract\PoolInterface;
use Hyperf\Pool\Exception\ConnectionException;
use Mine\Doctrine\Pool\Connection;
use Mine\Doctrine\Pool\ConnectionFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

describe('Connection (Final Class Testing)', function () {
    beforeEach(function () {
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->pool = Mockery::mock(PoolInterface::class);
        $this->config = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
        ];

        // Mock container dependencies
        $this->connectionFactory = Mockery::mock(ConnectionFactory::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->container->shouldReceive('get')
            ->with(ConnectionFactory::class)
            ->andReturn($this->connectionFactory);

        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->logger);

        // Create real Connection instance (it's final)
        $this->connection = new Connection($this->container, $this->pool, $this->config);
    });

    it('can be instantiated with dependencies', function () {
        expect($this->connection)->toBeInstanceOf(Connection::class);
    });

    it('reconnects successfully with valid configuration', function () {
        $dbalConnection = Mockery::mock(Doctrine\DBAL\Connection::class);

        $this->connectionFactory->shouldReceive('make')
            ->with($this->config)
            ->andReturn($dbalConnection);

        $result = $this->connection->reconnect();

        expect($result)->toBeTrue();
    });

    it('propagates connection factory errors during reconnect', function () {
        $this->connectionFactory->shouldReceive('make')
            ->with($this->config)
            ->andThrow(new RuntimeException('Database connection failed'));

        expect(fn () => $this->connection->reconnect())
            ->toThrow(RuntimeException::class, 'Database connection failed');
    });

    it('handles close operation gracefully', function () {
        // First establish a connection
        $dbalConnection = Mockery::mock(Doctrine\DBAL\Connection::class);
        $this->connectionFactory->shouldReceive('make')
            ->with($this->config)
            ->andReturn($dbalConnection);

        $this->connection->reconnect();

        // Mock the close operation
        $dbalConnection->shouldReceive('close')->once();

        $result = $this->connection->close();

        expect($result)->toBeTrue();
    });

    it('handles close when no connection exists', function () {
        // Should not fail when closing non-existent connection
        $result = $this->connection->close();

        expect($result)->toBeTrue();
    });

    it('throws connection exception when reconnect fails in getActiveConnection', function () {
        $this->connectionFactory->shouldReceive('make')
            ->with($this->config)
            ->andThrow(new RuntimeException('Connection failed'));

        expect(fn () => $this->connection->getActiveConnection())
            ->toThrow(ConnectionException::class, 'Connection reconnect failed.');
    });

    it('forwards method calls through __call', function () {
        // Test the __call magic method
        $result = $this->connection->someNonExistentMethod();

        // Should return null for non-existent methods
        expect($result)->toBeNull();
    });

    it('updates last use time on successful reconnect', function () {
        $dbalConnection = Mockery::mock(Doctrine\DBAL\Connection::class);
        $this->connectionFactory->shouldReceive('make')
            ->with($this->config)
            ->andReturn($dbalConnection);

        $timeBefore = microtime(true);
        $this->connection->reconnect();
        $timeAfter = microtime(true);

        // Use reflection to check lastUseTime was updated
        $reflection = new ReflectionClass($this->connection);
        $lastUseTimeProperty = $reflection->getProperty('lastUseTime');
        $lastUseTimeProperty->setAccessible(true);
        $lastUseTime = $lastUseTimeProperty->getValue($this->connection);

        expect($lastUseTime)->toBeGreaterThanOrEqual($timeBefore);
        expect($lastUseTime)->toBeLessThanOrEqual($timeAfter);
    });
});
