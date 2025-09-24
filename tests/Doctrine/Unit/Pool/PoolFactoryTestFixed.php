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
use Hyperf\DbConnection\Frequency;
use Mine\Doctrine\Config;
use Mine\Doctrine\Pool\Pool;
use Mine\Doctrine\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

describe('PoolFactory (Final Class Testing)', function () {
    beforeEach(function () {
        // Mock only interfaces, use real final classes
        $this->configInterface = Mockery::mock(ConfigInterface::class);
        $this->container = Mockery::mock(ContainerInterface::class);

        // Create real Config and PoolFactory instances
        $this->config = new Config($this->configInterface);
        $this->factory = new PoolFactory($this->config, $this->container);
    });

    it('throws exception when pool configuration not found', function () {
        $this->configInterface->shouldReceive('has')
            ->with('database.nonexistent')
            ->andReturn(false);

        expect(fn () => $this->factory->getPool('nonexistent'))
            ->toThrow(InvalidArgumentException::class, 'Database pool [nonexistent] not found');
    });

    it('creates pool when configuration exists', function () {
        $poolConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => ['min_connections' => 1, 'max_connections' => 5],
        ];

        // Mock the configuration calls
        $this->configInterface->shouldReceive('has')
            ->with('database.test_pool')
            ->andReturn(true);

        $this->configInterface->shouldReceive('set')
            ->with('database.test_pool.name', 'test_pool');

        $this->configInterface->shouldReceive('get')
            ->with('database.test_pool.option', [])
            ->andReturn($poolConfig['option']);

        $this->configInterface->shouldReceive('get')
            ->with('database.test_pool')
            ->andReturn($poolConfig);

        // Mock container dependencies for Pool creation
        $this->container->shouldReceive('make')
            ->with(Frequency::class, Mockery::any())
            ->andReturn(Mockery::mock(Frequency::class));

        $pool = $this->factory->getPool('test_pool');

        expect($pool)->toBeInstanceOf(Pool::class);
        expect($pool->getName())->toBe('test_pool');
    });

    it('caches pools and returns same instance', function () {
        $poolConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        // Setup mocks for pool creation (should only be called once)
        $this->configInterface->shouldReceive('has')
            ->with('database.cached_pool')
            ->once()
            ->andReturn(true);

        $this->configInterface->shouldReceive('set')
            ->with('database.cached_pool.name', 'cached_pool')
            ->once();

        $this->configInterface->shouldReceive('get')
            ->with('database.cached_pool.option', [])
            ->once()
            ->andReturn([]);

        $this->configInterface->shouldReceive('get')
            ->with('database.cached_pool')
            ->once()
            ->andReturn($poolConfig);

        $this->container->shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(Frequency::class));

        // First call should create the pool
        $pool1 = $this->factory->getPool('cached_pool');

        // Second call should return cached pool (no new mocks should be called)
        $pool2 = $this->factory->getPool('cached_pool');

        expect($pool1)->toBe($pool2);
    });

    it('creates different pools for different names', function () {
        $poolConfig = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
            'option' => [],
        ];

        // Mock for first pool
        $this->configInterface->shouldReceive('has')
            ->with('database.pool1')
            ->andReturn(true);
        $this->configInterface->shouldReceive('set')
            ->with('database.pool1.name', 'pool1');
        $this->configInterface->shouldReceive('get')
            ->with('database.pool1.option', [])
            ->andReturn([]);
        $this->configInterface->shouldReceive('get')
            ->with('database.pool1')
            ->andReturn($poolConfig);

        // Mock for second pool
        $this->configInterface->shouldReceive('has')
            ->with('database.pool2')
            ->andReturn(true);
        $this->configInterface->shouldReceive('set')
            ->with('database.pool2.name', 'pool2');
        $this->configInterface->shouldReceive('get')
            ->with('database.pool2.option', [])
            ->andReturn([]);
        $this->configInterface->shouldReceive('get')
            ->with('database.pool2')
            ->andReturn($poolConfig);

        $this->container->shouldReceive('make')
            ->twice()
            ->andReturn(Mockery::mock(Frequency::class));

        $pool1 = $this->factory->getPool('pool1');
        $pool2 = $this->factory->getPool('pool2');

        expect($pool1)->not->toBe($pool2);
        expect($pool1->getName())->toBe('pool1');
        expect($pool2->getName())->toBe('pool2');
    });
});
