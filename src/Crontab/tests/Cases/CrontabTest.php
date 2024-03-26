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

namespace Mine\Crontab\Tests\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Mine\Crontab\Crontab;
use Mine\Crontab\CrontabUrl;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabTest extends TestCase
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
        $connectionInterface->allows('table')->andReturnUsing(function ($table) use ($builder) {
            $this->assertSame(Crontab::TABLE, $table);
            return $builder;
        });
        ApplicationContext::getContainer()->set(ConnectionResolverInterface::class, $connectionResolverInterface);
    }

    public function testConstruct(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame($crontab->getCronId(), 1);
    }

    public function testGetBuilder(): void
    {
        $crontab = new Crontab(1);
        $crontab->getBuilder();
        $this->assertTrue(true);
    }

    public function testGetName(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame($crontab->getName(), 'xxx');
    }

    public function testGetMemo(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame($crontab->getMemo(), 'xxx');
    }

    public function testIsEnable(): void
    {
        $crontab = new Crontab(1);
        $this->assertTrue($crontab->isEnable());
        $this->assertFalse($crontab->isEnable());
    }

    public function testGetType(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame('xxx', $crontab->getType());
        $this->assertSame('callback', $crontab->getType());
        $this->assertSame('callback', $crontab->getType());
        $this->assertSame('callback', $crontab->getType());
        $this->assertSame('eval', $crontab->getType());
        $this->assertSame('command', $crontab->getType());
    }

    public function testGetCallback(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame($crontab->getCallback(), 'xxx');
        $this->assertSame($crontab->getCallback(), ['xxx', 'xxx']);
        $this->assertSame($crontab->getCallback(), [CrontabUrl::class, 'execute', ['http://baidu.com']]);
        $this->assertSame($crontab->getCallback(), ['AppTest', 'execute']);
        $this->assertSame($crontab->getCallback(), 'echo 1;');
        $this->assertSame($crontab->getCallback(), ['xxx', 'xxx']);
    }

    public function testGetRule(): void
    {
        $crontab = new Crontab(1);
        $this->assertSame($crontab->getRule(), '* * * * *');
        $this->assertSame($crontab->getRule(), '0 0 * * *');
    }

    public function testIsSingleton(): void
    {
        $crontab = new Crontab(1);
        $this->assertTrue($crontab->isSingleton());
        $this->assertFalse($crontab->isSingleton());
    }

    public function testIsOnOneServer(): void
    {
        $crontab = new Crontab(1);
        $this->assertTrue($crontab->isOnOneServer());
        $this->assertFalse($crontab->isOnOneServer());
    }
}
