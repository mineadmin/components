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
use Doctrine\DBAL\DriverManager;
use Hyperf\Support\SafeCaller;
use Mine\Doctrine\Pool\ConnectionFactory;

beforeEach(function () {
    $this->safeCaller = Mockery::mock(SafeCaller::class);
    $this->factory = new ConnectionFactory($this->safeCaller);
});

describe('ConnectionFactory', function () {
    it('creates DBAL connection with basic configuration', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'test_db',
            'user' => 'username',
            'password' => 'password',
        ];

        // Mock DriverManager::getConnection
        $connection = Mockery::mock(DBALConnection::class);

        // We can't easily mock static methods, so we'll test the method exists and returns a connection
        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles SQLite configuration', function () {
        $config = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
        ];

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles configuration with wrapper', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'wrapper' => static function (&$config) {
                $config['modified'] = true;
                return $config;
            },
        ];

        $this->safeCaller
            ->shouldReceive('call')
            ->with(Mockery::type(Closure::class))
            ->once()
            ->andReturn($config);

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles configuration without wrapper', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'test_db',
        ];

        // SafeCaller should not be called when no wrapper exists
        $this->safeCaller
            ->shouldNotReceive('call');

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('processes wrapper configuration through parser', function () {
        $originalConfig = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'wrapper' => static function (&$config) {
                $config['processed'] = true;
            },
        ];

        $this->safeCaller
            ->shouldReceive('call')
            ->once()
            ->andReturn(null);

        $result = $this->factory->make($originalConfig);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles complex configuration arrays', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => 3306,
            'dbname' => 'test_db',
            'user' => 'username',
            'password' => 'password',
            'charset' => 'utf8mb4',
            'driverOptions' => [
                'ssl_mode' => 'REQUIRED',
            ],
        ];

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('propagates DBAL connection errors', function () {
        $invalidConfig = [
            'driver' => 'invalid_driver',
            'host' => 'nonexistent_host',
        ];

        expect(fn () => $this->factory->make($invalidConfig))
            ->toThrow(Exception::class);
    });

    it('handles empty configuration gracefully', function () {
        $emptyConfig = [];

        expect(fn () => $this->factory->make($emptyConfig))
            ->toThrow(Exception::class);
    });

    it('handles configuration with custom options', function () {
        $config = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'memory' => true,
            'driverOptions' => [
                'foreign_key_checks' => 1,
            ],
        ];

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('processes wrapper that modifies configuration', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'wrapper' => static function (&$cfg) {
                $cfg['host'] = 'modified_host';
                $cfg['port'] = 3307;
                return $cfg;
            },
        ];

        $this->safeCaller
            ->shouldReceive('call')
            ->with(Mockery::type(Closure::class))
            ->once()
            ->andReturnUsing(static function ($closure) {
                return $closure();
            });

        $result = $this->factory->make($config);

        expect($result)->toBeInstanceOf(DBALConnection::class);
    });

    it('handles wrapper with exception gracefully', function () {
        $config = [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'wrapper' => static function () {
                throw new RuntimeException('Wrapper failed');
            },
        ];

        $this->safeCaller
            ->shouldReceive('call')
            ->with(Mockery::type(Closure::class))
            ->once()
            ->andThrow(new RuntimeException('Wrapper failed'));

        expect(fn () => $this->factory->make($config))
            ->toThrow(RuntimeException::class, 'Wrapper failed');
    });
});
