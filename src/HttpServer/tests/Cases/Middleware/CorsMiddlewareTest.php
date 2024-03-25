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

use Hyperf\Context\Context;
use Mine\HttpServer\Middleware\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 * @coversNothing
 */
class CorsMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $corsMiddleware = new CorsMiddleware();
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->allows('getMethod')->andReturn('GET', 'OPTIONS');
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $handler->allows('handle')->andReturn($response);
        $response->allows('withHeader')
            ->andReturnUsing(function ($k, $v) use ($response) {
                if ($k === 'Access-Control-Allow-Origin') {
                    $this->assertSame($v, '*');
                }
                if ($k === 'Access-Control-Allow-Methods') {
                    $this->assertSame($v, 'GET,PUT,POST,DELETE,OPTIONS');
                }
                if ($k === 'Access-Control-Allow-Credentials') {
                    $this->assertSame($v, 'true');
                }
                if ($k === 'Access-Control-Allow-Headers') {
                    $this->assertSame($v, 'accept-language,authorization,lang,uid,token,Keep-Alive,User-Agent,Cache-Control,Content-Type');
                }
                return $response;
            });
        Context::set(ResponseInterface::class, $response);
        $corsMiddleware->process($request, $handler);
        $corsMiddleware->process($request, $handler);
    }
}
