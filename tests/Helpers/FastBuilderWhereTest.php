<?php

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;

beforeEach(function (){
    $this->query = new \Hyperf\Database\Query\Builder(
        Mockery::mock(ConnectionInterface::class),
        Mockery::mock(Grammar::class),
        Mockery::mock(Processor::class)
    );
    $this->mock = new \Mine\Helper\FastBuilderWhere($this->query,[
        'username'  =>  '123456',
        'date'  =>  [
            '2023-01-22',
            '2023-02-22'
        ],
        'datetime'  =>  [
            '2023-01-22 00:00:00',
            '2023-01-22 23:59:59',
        ],
        'timestamps'    =>  [
            \Carbon\Carbon::now()->startOfDay()->timestamp,
            \Carbon\Carbon::now()->endOfDay()->timestamp,
        ]
    ]);
});

test('mock',function (){
    $query = $this->query;
    $this->mock->ge('username');
    expect(in_array('123456',$query->getBindings(),true))
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
    expect(in_array('123456',$query->getBindings(),true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(3);
    $this->mock->ne('username');
    expect(in_array('123456',$query->getBindings(),true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(4);
    $this->mock->lt('username');
    expect(in_array('123456',$query->getBindings(),true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(5);
    $this->mock
        ->lt('username')
        ->eq('username')
        ->gt('username')
        ->le('username')
        ->ge('username');
    expect(in_array('123456',$query->getBindings(),true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(10);
    $this->mock->timestampsRange('timestamps','timestamps')
            ->dateRange('date','date')
            ->datetimeRange('datetime','datetime');

    expect(in_array('123456',$query->getBindings(),true))
        ->toBeTrue()
        ->and(count($query->getBindings()))
        ->toEqual(16);
});