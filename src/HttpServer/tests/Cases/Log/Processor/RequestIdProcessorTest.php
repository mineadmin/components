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

namespace Mine\HttpServer\Tests\Cases\Log\Processor;

use Hyperf\Context\ApplicationContext;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Log\Processor\RequestIdProcessor;
use Mine\HttpServer\Log\RequestIdGenerator;
use Mine\HttpServer\RequestIdHolder;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RequestIdProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::getContainer()
            ->define(
                RequestIdGeneratorInterface::class,
                RequestIdGenerator::class
            );
    }

    public function testInvoke(): void
    {
        $requestIdProcessor = new RequestIdProcessor();
        $logRecord = new LogRecord(
            \Mockery::mock(\DateTimeImmutable::class),
            'xxx',
            Level::Alert,
            'xxx'
        );
        $requestIdProcessor($logRecord);
        $this->assertArrayHasKey('request_id', $logRecord->extra);
        $this->assertSame($logRecord->extra['request_id'], RequestIdHolder::getId());
    }
}
