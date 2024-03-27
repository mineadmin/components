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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpServer\Annotation\Controller;
use Mine\HttpServer\Exception\BusinessException;
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
        $stdClass = new \stdClass();
        $stdClass->server = 'http';
        $stdClass->prefix = 'test/index';
        AnnotationCollector::collectClass(
            Test::class,
            Controller::class,
            $stdClass
        );
        $request = \Mockery::mock(ServerRequestInterface::class);
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $uri = \Mockery::mock(UriInterface::class);
        $uri->allows('getPath')->andReturn('/favicon.ico', '/test/index');
        $request->allows('getUri')->andReturn($uri);
        $response = \Mockery::mock(ResponseInterface::class);
        $handler->allows('handle')->andReturn($response);
        $middleware->process($request, $handler);
        try {
            $middleware->process($request, $handler);
        } catch (\Exception $e) {
            $this->assertInstanceOf(BusinessException::class, $e);
            $this->assertSame($e->getMessage(), '模块被禁用');
        }
        $this->assertTrue(true);
    }
}
