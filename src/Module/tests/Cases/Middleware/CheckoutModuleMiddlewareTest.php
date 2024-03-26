<?php

namespace Mine\Module\Tests\Cases\Middleware;

use Mine\Module\Middleware\CheckoutModuleMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckoutModuleMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $middleware = new CheckoutModuleMiddleware();
        $request = \Mockery::mock(ServerRequestInterface::class);
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $handler->allows('handle')->andReturn($response);
        $middleware->process($request,$handler);
        $this->assertTrue(true);
    }
}