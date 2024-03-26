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

namespace Mine\Module\Tests\Cases\Middleware;

use Mine\Module\Middleware\CheckoutModuleMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 * @coversNothing
 */
class CheckoutModuleMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $middleware = new CheckoutModuleMiddleware();
        $request = \Mockery::mock(ServerRequestInterface::class);
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $uri = \Mockery::mock(UriInterface::class);
        $uri->allows('getPath')->andReturn('/favicon.ico', '/test/index');
        $request->allows('getUri')->andReturn($uri);
        $response = \Mockery::mock(ResponseInterface::class);
        $handler->allows('handle')->andReturn($response);
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
        $this->assertTrue(true);
    }
}
