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

namespace Mine\HttpServer\Exception;

use Mine\HttpServer\Constant\HttpResultCode;
use Mine\HttpServer\RequestIdHolder;

class BusinessException extends HttpException
{
    public function __construct(
        string $message = '',
        HttpResultCode|int $code = HttpResultCode::NORMAL_STATUS,
        ?\Throwable $previous = null
    ) {
        if ($code instanceof HttpResultCode) {
            $message = $code->getTrans();
        }
        parent::__construct($message, $code, $previous);
    }

    public function result(): array
    {
        return [
            'request_id' => RequestIdHolder::getId(),
            '',
        ];
    }
}
