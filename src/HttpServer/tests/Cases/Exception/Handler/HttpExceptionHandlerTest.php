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

namespace Mine\HttpServer\Tests\Cases\Exception\Handler;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Exception\Handler\HttpExceptionHandler;
use Mine\HttpServer\Exception\HttpException;
use Mine\HttpServer\Log\RequestIdGenerator;
use Mine\HttpServer\RequestIdHolder;
use PHPUnit\Framework\TestCase;
use Swow\Psr7\Message\ResponsePlusInterface;

/**
 * @internal
 * @coversNothing
 */
class HttpExceptionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::getContainer()
            ->define(
                RequestIdGeneratorInterface::class,
                RequestIdGenerator::class
            );
    }

    public function testIsValid(): void
    {
        $reflection = new \ReflectionClass(HttpExceptionHandler::class);
        $method = $reflection->getMethod('isValid');
        $instance = \Mockery::mock(HttpExceptionHandler::class);
        $this->assertTrue($method->invoke($instance, \Mockery::mock(HttpException::class)));
        $this->assertFalse($method->invoke($instance, new \Exception()));
        $this->assertTrue($method->invoke($instance, new class extends HttpException {}));
    }

    public function testHandle(): void
    {
        $requestId = RequestIdHolder::getId();
        $reflection = new \ReflectionClass(HttpExceptionHandler::class);
        $method = $reflection->getMethod('handle');
        $instance = \Mockery::mock(HttpExceptionHandler::class);
        $response = \Mockery::mock(ResponsePlusInterface::class);
        $response->allows('setBody')->withArgs(function (SwooleStream $stream) use ($requestId) {
            $this->assertInstanceOf(SwooleStream::class, $stream);
            $this->assertSame($stream->getContents(), '{"success":false,"requestId":"' . $requestId . '","message":"xxx","code":100}');
            return true;
        })->andReturn($response);
        $method->invoke(
            $instance,
            new class extends HttpException {
                protected $message = 'xxx';

                protected $code = 100;
            },
            $response
        );
    }
}
