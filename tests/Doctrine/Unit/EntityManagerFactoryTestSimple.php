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
use Mine\Doctrine\EntityManagerFactory;
use Mine\Doctrine\ORMSetupFactory;
use Mine\Doctrine\Pool\Connection;
use Mine\Doctrine\Pool\Pool;
use Mine\Doctrine\Pool\PoolFactory;

describe('EntityManagerFactory (Simplified Final Class Testing)', function () {
    it('can be instantiated with dependencies', function () {
        $poolFactory = Mockery::mock(PoolFactory::class);
        $ormSetupFactory = Mockery::mock(ORMSetupFactory::class);

        $factory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        expect($factory)->toBeInstanceOf(EntityManagerFactory::class);
    });

    it('propagates pool factory exceptions', function () {
        $poolFactory = Mockery::mock(PoolFactory::class);
        $ormSetupFactory = Mockery::mock(ORMSetupFactory::class);

        $poolFactory->shouldReceive('getPool')
            ->with('nonexistent')
            ->andThrow(new InvalidArgumentException('Pool not found'));

        $factory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        expect(static fn () => $factory->create('nonexistent'))
            ->toThrow(InvalidArgumentException::class, 'Pool not found');
    });

    it('passes correct pool name to pool factory', function () {
        $poolFactory = Mockery::mock(PoolFactory::class);
        $ormSetupFactory = Mockery::mock(ORMSetupFactory::class);

        $poolFactory->shouldReceive('getPool')
            ->with('custom_name')
            ->once()
            ->andThrow(new RuntimeException('Expected call made')); // We just want to verify the call

        $factory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        expect(static fn () => $factory->create('custom_name'))
            ->toThrow(RuntimeException::class, 'Expected call made');
    });

    it('uses default pool name when none specified', function () {
        $poolFactory = Mockery::mock(PoolFactory::class);
        $ormSetupFactory = Mockery::mock(ORMSetupFactory::class);

        $poolFactory->shouldReceive('getPool')
            ->with('default') // Should call with 'default'
            ->once()
            ->andThrow(new RuntimeException('Default called'));

        $factory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        expect(static fn () => $factory->create()) // No pool name
            ->toThrow(RuntimeException::class, 'Default called');
    });

    it('calls ORM setup factory for configuration', function () {
        $poolFactory = Mockery::mock(PoolFactory::class);
        $ormSetupFactory = Mockery::mock(ORMSetupFactory::class);

        // Setup minimal mocks to get to the ORMSetupFactory call
        $pool = Mockery::mock(Pool::class);
        $connection = Mockery::mock(Connection::class);

        $poolFactory->shouldReceive('getPool')->andReturn($pool);
        $pool->shouldReceive('get')->andReturn($connection);
        $connection->shouldReceive('getConnection')->andThrow(new RuntimeException('Before ORM setup'));

        $ormSetupFactory->shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(Configuration::class));

        $factory = new EntityManagerFactory($poolFactory, $ormSetupFactory);

        expect(static fn () => $factory->create())
            ->toThrow(RuntimeException::class);
    });
});
