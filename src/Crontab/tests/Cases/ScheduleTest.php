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

use Hyperf\Collection\Collection;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\Crontab\Schedule;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[RequiresPhpExtension('swoole','< 6.0')]
class ScheduleTest extends TestCase
{
    use RunTestsInCoroutine;

    protected function setUp(): void
    {
        $config = new Config([]);
        ApplicationContext::getContainer()->set(ConfigInterface::class, $config);
    }

    public function testGetCrontab(): void
    {
        $connectionResolverInterface = \Mockery::mock(ConnectionResolverInterface::class);
        $connectionInterface = \Mockery::mock(ConnectionInterface::class);
        $connectionResolverInterface
            ->allows('connection')
            ->andReturn($connectionInterface);
        $connectionInterface->allows('table')->andReturnUsing(function ($table) {
            $this->assertSame($table, Schedule::CRONTAB_TABLE);
            $builder = \Mockery::mock(Builder::class);
            $stdclass = new \stdClass();
            $stdclass->id = 1;
            $builder->allows('get')
                ->andReturn(new Collection([$stdclass]));
            $builder->allows('where')->andReturnUsing(function ($column, $val) use ($builder) {
                $this->assertSame($column, 'status');
                $this->assertSame($val, 1);
                return $builder;
            });
            return $builder;
        }, function ($table) {
            $this->assertSame($table, Schedule::CRONTAB_TABLE);
            $builder = \Mockery::mock(Builder::class);
            $builder->allows('get')
                ->andReturn(new Collection([]));
            $builder->allows('where')->andReturnUsing(function ($column, $val) use ($builder) {
                $this->assertSame($column, 'status');
                $this->assertSame($val, 1);
                return $builder;
            });
            return $builder;
        });
        ApplicationContext::getContainer()->set(ConnectionResolverInterface::class, $connectionResolverInterface);

        $schedule = new \ReflectionClass(Schedule::class);
        $method = $schedule->getMethod('getCrontab');
        $instance = \Mockery::mock(Schedule::class);
        $result = $method->invoke($instance);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $result = $method->invoke($instance);
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
