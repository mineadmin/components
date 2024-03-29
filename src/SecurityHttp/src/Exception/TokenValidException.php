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

namespace Mine\Security\Http\Exception;

class TokenValidException extends \RuntimeException
{
    public function __construct($code = 0, $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
