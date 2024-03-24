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

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use Mine\HttpServer\Config;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestIdMiddleware implements MiddlewareInterface
{
    public const REQUEST_ID = 'request.id';

    public function __construct(
        private readonly RequestIdGeneratorInterface $requestIdGenerator,
        private readonly Config $config
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $this->requestIdGenerator->generate();
        if ($this->config->get('requestId.withAttribute', true) === true) {
            $request = $request->withAttribute(self::REQUEST_ID, $requestId);
            RequestContext::set($request);
        }
        Context::set(self::REQUEST_ID, $requestId);
        $response = $handler->handle($request);
        if ($this->config->get('requestId.withHeader', true)) {
            $response = $response->withAddedHeader('Request-Id', $requestId);
        }
        ResponseContext::set($response);
        return $response;
    }
}
