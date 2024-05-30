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

namespace Mine\Crontab\Tests\Cases\Listener;

use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Engine\Coroutine;
use Hyperf\Process\ProcessManager;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\Crontab\Listener\CrontabProcessStarredListener;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[RequiresPhpExtension('swoole', '< 6.0')]
class CrontabProcessStarredListenerTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testListen(): void
    {
        $reflectionClass = new \ReflectionClass(CrontabProcessStarredListener::class);
        $instance = \Mockery::mock(CrontabProcessStarredListener::class);
        $method = $reflectionClass->getMethod('listen');
        $result = $method->invoke($instance);
        $this->assertSame($result, [
            CrontabDispatcherStarted::class,
        ]);
    }

    public function testProcess(): void
    {
        $reflectionClass = new \ReflectionClass(CrontabProcessStarredListener::class);
        $instance = \Mockery::mock(CrontabProcessStarredListener::class);
        CrontabProcessStarredListener::$sleep = 1;
        $instance->allows('registerCrontab');
        $method = $reflectionClass->getMethod('process');
        ProcessManager::setRunning(false);
        $method->invoke($instance, \Mockery::mock(CrontabDispatcherStarted::class));
        ProcessManager::setRunning(true);
        Coroutine::create(function () {
            sleep(2);
            ProcessManager::setRunning(false);
        });
        $method->invoke($instance, \Mockery::mock(CrontabDispatcherStarted::class));
        $this->assertTrue(true);
    }
}
