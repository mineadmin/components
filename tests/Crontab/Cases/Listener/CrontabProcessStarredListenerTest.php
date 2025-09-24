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
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Engine\Coroutine;
use Hyperf\Process\ProcessManager;
use Mine\Crontab\Listener\CrontabProcessStarredListener;

test('listen', static function () {
    $reflectionClass = new ReflectionClass(CrontabProcessStarredListener::class);
    $instance = Mockery::mock(CrontabProcessStarredListener::class);
    $method = $reflectionClass->getMethod('listen');
    $result = $method->invoke($instance);
    expect($result)->toBe([
        CrontabDispatcherStarted::class,
    ]);
});

test('process', static function () {
    $reflectionClass = new ReflectionClass(CrontabProcessStarredListener::class);
    $instance = Mockery::mock(CrontabProcessStarredListener::class);
    CrontabProcessStarredListener::$sleep = 1;
    $instance->allows('registerCrontab');
    $method = $reflectionClass->getMethod('process');
    ProcessManager::setRunning(false);
    $method->invoke($instance, Mockery::mock(CrontabDispatcherStarted::class));
    ProcessManager::setRunning(true);
    Coroutine::create(static function () {
        sleep(2);
        ProcessManager::setRunning(false);
    });
    $method->invoke($instance, Mockery::mock(CrontabDispatcherStarted::class));
    expect(true)->toBeTrue();
});
