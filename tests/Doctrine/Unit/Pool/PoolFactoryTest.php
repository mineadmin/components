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
use Hyperf\DbConnection\Frequency;
use Mine\Doctrine\Config;
use Mine\Doctrine\Pool\Pool;
use Mine\Doctrine\Pool\PoolFactory;

describe('PoolFactory', function () {
    beforeEach(function () {
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->config = new Config($this->configInterface);
        $this->container = Mockery::mock(ContainerInterface::class);
        $this->factory = new PoolFactory($this->config, $this->container);

        // Setup common container mocks
        $this->container->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));
    });

    it('creates new pool when not cached', function () {
        $poolName = 'test_pool';
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with("doctrine.database.{$poolName}")
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with("doctrine.database.{$poolName}.name", $poolName);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}.option")
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}")
            ->andReturn($databaseConfig);

        $pool = $this->factory->getPool($poolName);

        expect($pool)->toBeInstanceOf(Pool::class);
        expect($pool->getName())->toBe($poolName);
    });

    it('returns cached pool when already created', function () {
        $poolName = 'cached_pool';
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with("doctrine.database.{$poolName}")
            ->once()
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with("doctrine.database.{$poolName}.name", $poolName)
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}.option")
            ->once()
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}")
            ->once()
            ->andReturn($databaseConfig);

        // First call should create the pool
        $pool1 = $this->factory->getPool($poolName);

        // Second call should return the cached pool
        $pool2 = $this->factory->getPool($poolName);

        expect($pool1)->toBeInstanceOf(Pool::class);
        expect($pool2)->toBeInstanceOf(Pool::class);
        expect($pool1)->toBe($pool2); // Should be the exact same instance
    });

    it('creates different pools for different names', function () {
        $poolName1 = 'pool1';
        $poolName2 = 'pool2';
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        // Setup for first pool
        $this->configInterface
            ->shouldReceive('has')
            ->with("doctrine.database.{$poolName1}")
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with("doctrine.database.{$poolName1}.name", $poolName1);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName1}.option")
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName1}")
            ->andReturn($databaseConfig);

        // Setup for second pool
        $this->configInterface
            ->shouldReceive('has')
            ->with("doctrine.database.{$poolName2}")
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with("doctrine.database.{$poolName2}.name", $poolName2);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName2}.option")
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName2}")
            ->andReturn($databaseConfig);

        $pool1 = $this->factory->getPool($poolName1);
        $pool2 = $this->factory->getPool($poolName2);

        expect($pool1)->toBeInstanceOf(Pool::class);
        expect($pool2)->toBeInstanceOf(Pool::class);
        expect($pool1)->not->toBe($pool2); // Should be different instances
        expect($pool1->getName())->toBe($poolName1);
        expect($pool2->getName())->toBe($poolName2);
    });

    it('propagates pool creation exceptions', function () {
        $poolName = 'invalid_pool';

        $this->configInterface
            ->shouldReceive('has')
            ->with('doctrine.database.invalid_pool')
            ->andReturn(false);

        expect(fn () => $this->factory->getPool($poolName))
            ->toThrow(InvalidArgumentException::class, 'Database pool [invalid_pool] not found');
    });

    it('maintains pool cache integrity across multiple calls', function () {
        $poolName = 'persistent_pool';
        $databaseConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        $this->configInterface
            ->shouldReceive('has')
            ->with("doctrine.database.{$poolName}")
            ->once()
            ->andReturn(true);

        $this->configInterface
            ->shouldReceive('set')
            ->with("doctrine.database.{$poolName}.name", $poolName)
            ->once();

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}.option")
            ->once()
            ->andReturn([]);

        $this->configInterface
            ->shouldReceive('get')
            ->with("doctrine.database.{$poolName}")
            ->once()
            ->andReturn($databaseConfig);

        // Multiple calls should all return the same cached instance
        $pool1 = $this->factory->getPool($poolName);
        $pool2 = $this->factory->getPool($poolName);
        $pool3 = $this->factory->getPool($poolName);

        expect($pool1)->toBe($pool2);
        expect($pool2)->toBe($pool3);
        expect($pool1)->toBe($pool3);
    });
});
