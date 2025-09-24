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
use Hyperf\Collection\Collection;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Mine\Crontab\Schedule;

beforeEach(static function () {
    $config = new Config([]);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $config);
});

test('get crontab', static function () {
    $connectionResolverInterface = Mockery::mock(ConnectionResolverInterface::class);
    $connectionInterface = Mockery::mock(ConnectionInterface::class);
    $connectionResolverInterface
        ->allows('connection')
        ->andReturn($connectionInterface);
    $connectionInterface->allows('table')->andReturnUsing(static function ($table) {
        expect($table)->toBe(Schedule::CRONTAB_TABLE);
        $builder = Mockery::mock(Builder::class);
        $stdclass = new stdClass();
        $stdclass->id = 1;
        $builder->allows('get')
            ->andReturn(new Collection([$stdclass]));
        $builder->allows('where')->andReturnUsing(static function ($column, $val) use ($builder) {
            expect($column)->toBe('status');
            expect($val)->toBe(1);
            return $builder;
        });
        return $builder;
    }, static function ($table) {
        expect($table)->toBe(Schedule::CRONTAB_TABLE);
        $builder = Mockery::mock(Builder::class);
        $builder->allows('get')
            ->andReturn(new Collection([]));
        $builder->allows('where')->andReturnUsing(static function ($column, $val) use ($builder) {
            expect($column)->toBe('status');
            expect($val)->toBe(1);
            return $builder;
        });
        return $builder;
    });
    ApplicationContext::getContainer()->set(ConnectionResolverInterface::class, $connectionResolverInterface);

    $schedule = new ReflectionClass(Schedule::class);
    $method = $schedule->getMethod('getCrontab');
    $instance = Mockery::mock(Schedule::class);
    $result = $method->invoke($instance);
    expect($result)->toBeArray();
    expect($result)->toHaveCount(1);
    $result = $method->invoke($instance);
    expect($result)->toBeArray();
    expect($result)->toHaveCount(0);
});
