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

namespace Mine\Crontab\Tests\Cases\Aspect;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mine\Crontab\Aspect\CrontabExecutorAspect;
use Mine\Crontab\Crontab;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabExecutorAspectTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::getContainer()->set(ConfigInterface::class, new Config([]));
        $connectionResolverInterface = \Mockery::mock(ConnectionResolverInterface::class);
        $connectionInterface = \Mockery::mock(ConnectionInterface::class);
        $connectionResolverInterface
            ->allows('connection')
            ->andReturn($connectionInterface);
        $builder = \Mockery::mock(Builder::class);
        $connectionInterface->allows('table')->andReturn($builder);
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
        $builder->allows('insert')->andReturnUsing(function ($data) {
            return true;
        });
        ApplicationContext::getContainer()->set(ConnectionResolverInterface::class, $connectionResolverInterface);
    }

    public function testProcess(): void
    {
        $aspect = new CrontabExecutorAspect();
        $this->assertSame($aspect->classes, [
            Executor::class . '::logResult',
        ]);
        $proceedingJoinPoint = \Mockery::mock(ProceedingJoinPoint::class);
        $proceedingJoinPoint->allows('process');
        $proceedingJoinPoint->allows('getArguments')->andReturn([
            new Crontab(1),
            true,
            new \Exception('test'),
        ]);
        $aspect->process($proceedingJoinPoint);
        $aspect->process($proceedingJoinPoint);
        $aspect->process($proceedingJoinPoint);
        $aspect->process($proceedingJoinPoint);
    }
}
