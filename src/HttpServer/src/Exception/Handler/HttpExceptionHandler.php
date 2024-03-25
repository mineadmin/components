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

namespace Mine\HttpServer\Exception\Handler;

use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterExceptionHandler;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Mine\HttpServer\Exception\HttpException;
use Mine\HttpServer\Response;
use Psr\Http\Message\ResponseInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

#[RegisterExceptionHandler]
class HttpExceptionHandler extends ExceptionHandler
{
    public function __construct(
        private readonly Response $response
    ) {}

    public function handle(\Throwable $throwable, ResponsePlusInterface $response): ResponseInterface
    {
        return $this
            ->response
            ->error(
                message: $throwable->getMessage(),
                code: $throwable->getCode(),
                data: $throwable->result() ?? []
            );
    }

    public function isValid(\Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
