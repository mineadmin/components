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
use Carbon\Carbon;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use Mine\Helper\FastBuilderWhere;

beforeEach(function () {
    $this->query = new Builder(
        Mockery::mock(ConnectionInterface::class),
        Mockery::mock(Grammar::class),
        Mockery::mock(Processor::class)
    );
    $this->mock = new FastBuilderWhere($this->query, [
        'username' => '123456',
        'date' => [
            '2023-01-22',
            '2023-02-22',
        ],
        'datetime' => [
            '2023-01-22 00:00:00',
            '2023-01-22 23:59:59',
        ],
        'timestamps' => [
            Carbon::now()->startOfDay()->timestamp,
            Carbon::now()->endOfDay()->timestamp,
        ],
    ]);
});

test('mock', function () {
    $query = $this->query;
    $this->mock->ge('username');
    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(1);
    $this->mock->ge('username');
    expect(count($query->getBindings()))
        ->toEqual(2);
    $this->mock->ge('user');
    expect(count($query->getBindings()))
        ->toEqual(2);
    $this->mock->lt('username');
    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(3);
    $this->mock->ne('username');
    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(4);
    $this->mock->lt('username');
    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(5);
    $this->mock->like('username');
    expect(in_array('%123456%', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(6);
    $this->mock->likeRight('username');
    expect(in_array('123456%', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(7);
    $this->mock->likeLeft('username');
    expect(in_array('%123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(8);
    $this->mock
        ->lt('username')
        ->eq('username')
        ->gt('username')
        ->le('username')
        ->ge('username');
    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(13);
    $this->mock->timestampsRange('timestamps', 'timestamps')
        ->dateRange('date', 'date')
        ->datetimeRange('datetime', 'datetime');

    expect(in_array('123456', $query->getBindings(), true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(19);
});
