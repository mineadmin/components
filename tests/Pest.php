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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\Tests\TestCase;
use Psr\Log\LogLevel;

uses(TestCase::class, RunTestsInCoroutine::class)
    ->beforeEach(static function () {
        $mockConfig = Mockery::mock(ConfigInterface::class);
        $mockConfig->allows('has')->andReturn(true);
        $mockConfig->allows('get')->andReturn([
            StdoutLoggerInterface::class => [
                LogLevel::DEBUG,
            ],
        ]);
        $mockConfig->allows('set')->andReturn(true);

        // Mock the container itself
        $mockContainer = Mockery::mock(ContainerInterface::class);
        $mockContainer->allows('set')->andReturn(true);
        $mockContainer->allows('get')->andReturn($mockConfig);
        $mockContainer->allows('has')->andReturn(false); // Default to false for unknown services
        $mockContainer->allows('make')->andReturnUsing(static function ($class, $parameters = []) {
            return new $class(...array_values($parameters));
        });

        // Replace the container
        ApplicationContext::setContainer($mockContainer);
    })
    ->afterEach(static function () {
        Mockery::close();
    })
    ->in('Feature');
