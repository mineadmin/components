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

namespace Mine\HttpServer\Tests\Cases\Middleware;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Log\RequestIdGenerator;
use Mine\HttpServer\Middleware\RequestIdMiddleware;
use Mine\HttpServer\RequestIdHolder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

/**
 * @internal
 * @coversNothing
 */
class RequestIdMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::getContainer()
            ->define(
                RequestIdGeneratorInterface::class,
                RequestIdGenerator::class
            );
    }

    public function testProcess(): void
    {
        $middleware = new RequestIdMiddleware();
        $response = \Mockery::mock(ResponsePlusInterface::class);
        $response
            ->allows('withHeader')
            ->andReturn($response);
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->allows('handle')
            ->andReturn($response);
        $request = \Mockery::mock(ServerRequestInterface::class);
        $middleware->process($request, $handler);
        $this->assertSame(
            Context::get(RequestIdGeneratorInterface::REQUEST_ID),
            RequestIdHolder::getId()
        );
    }
}
