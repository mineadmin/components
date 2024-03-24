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

namespace Mine\HttpServer\Middleware;

use Hyperf\Context\RequestContext;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InitialRequestIdMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RequestIdGeneratorInterface $requestIdGenerator
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $this->requestIdGenerator->generate();
        $request = $request->withAttribute('request_id', $requestId);
        RequestContext::set($request);
        return $handler->handle($request);
    }
}
