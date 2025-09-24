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
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Mine\Crontab\Crontab;
use Mine\Crontab\CrontabUrl;

beforeEach(static function () {
    ApplicationContext::getContainer()->set(ConfigInterface::class, new Config([]));
    $connectionResolverInterface = Mockery::mock(ConnectionResolverInterface::class);
    $connectionInterface = Mockery::mock(ConnectionInterface::class);
    $connectionResolverInterface
        ->allows('connection')
        ->andReturn($connectionInterface);
    $builder = Mockery::mock(Builder::class);
    $builder->allows('where')->with(Crontab::TABLE_KEY, 1)->andReturn($builder);
    $builder->allows('value')->with(Crontab::ENABLE_COLUMN)->andReturn(1, 0);
    $builder->allows('value')->with(Crontab::IS_SINGLETON)->andReturn(1, 0);
    $builder->allows('value')->with(Crontab::IS_ON_ONE_SERVER_COLUMN)->andReturn(1, 0);
    $builder->allows('value')->with(Crontab::NAME_COLUMN)->andReturn('xxx');
    $builder->allows('value')->with(Crontab::MEMO_COLUMN)->andReturn('xxx');
    $builder->allows('value')
        ->with(Crontab::RULE_COLUMN)
        ->andReturn('* * * * *', '0 0 * * *');
    $builder->allows('value')
        ->with(Crontab::TYPE_COLUMN)
        ->andReturn(
            'xxx',
            'callback',
            'url',
            'class',
            'eval',
            'command',
            'xxx',
            'callback',
            'url',
            'class',
            'eval',
            'command'
        );
    $builder->allows('value')
        ->with(Crontab::VALUE_COLUMN)
        ->andReturn(
            'xxx',
            '["xxx","xxx"]',
            'http://baidu.com',
            'AppTest',
            'echo 1;',
            '["xxx","xxx"]'
        );
    $connectionInterface->allows('table')->andReturnUsing(static function ($table) use ($builder) {
        expect($table)->toBe(Crontab::TABLE);
        return $builder;
    });
    ApplicationContext::getContainer()->set(ConnectionResolverInterface::class, $connectionResolverInterface);
});

test('construct', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getCronId())->toBe(1);
});

test('get builder', static function () {
    $crontab = new Crontab(1);
    $crontab->getBuilder();
    expect(true)->toBeTrue();
});

test('get name', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getName())->toBe('xxx');
});

test('get memo', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getMemo())->toBe('xxx');
});

test('is enable', static function () {
    $crontab = new Crontab(1);
    expect($crontab->isEnable())->toBeTrue();
    expect($crontab->isEnable())->toBeFalse();
});

test('get type', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getType())->toBe('xxx');
    expect($crontab->getType())->toBe('callback');
    expect($crontab->getType())->toBe('callback');
    expect($crontab->getType())->toBe('callback');
    expect($crontab->getType())->toBe('eval');
    expect($crontab->getType())->toBe('command');
});

test('get callback', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getCallback())->toBe('xxx');
    expect($crontab->getCallback())->toBe(['xxx', 'xxx']);
    expect($crontab->getCallback())->toBe([CrontabUrl::class, 'execute', ['http://baidu.com']]);
    expect($crontab->getCallback())->toBe(['AppTest', 'execute']);
    expect($crontab->getCallback())->toBe('echo 1;');
    expect($crontab->getCallback())->toBe(['xxx', 'xxx']);
});

test('get rule', static function () {
    $crontab = new Crontab(1);
    expect($crontab->getRule())->toBe('* * * * *');
    expect($crontab->getRule())->toBe('0 0 * * *');
});

test('is singleton', static function () {
    $crontab = new Crontab(1);
    expect($crontab->isSingleton())->toBeTrue();
    expect($crontab->isSingleton())->toBeFalse();
});

test('is on one server', static function () {
    $crontab = new Crontab(1);
    expect($crontab->isOnOneServer())->toBeTrue();
    expect($crontab->isOnOneServer())->toBeFalse();
});
