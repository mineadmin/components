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

use Mine\HttpServer\Middleware\JsonMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

/**
 * @internal
 * @coversNothing
 */
class JsonMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $middleware = new JsonMiddleware();
        $response = \Mockery::mock(ResponsePlusInterface::class);
        $response
            ->allows('withHeader')
            ->withArgs(function ($key, $value) {
                $this->assertSame($key, 'content-type');
                $this->assertSame($value, 'application/json; charset=utf-8');
                return true;
            })->andReturn($response);
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->allows('handle')
            ->andReturn($response);
        $request = \Mockery::mock(ServerRequestInterface::class);
        $middleware->process($request, $handler);
    }
}
