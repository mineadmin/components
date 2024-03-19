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

namespace Mine\Translatable\Exception;

class LocalesNotDefinedException extends \Exception
{
    public function __construct($message = '', $code = 0, ?\Throwable $previous = null)
    {
        $message = empty($message)
            ? 'Please make sure you have run `php bin/hyperf.php vendor:publish xmo/mine-translatable` and that the locales configuration is defined.'
            : $message;
        parent::__construct($message, $code, $previous);
    }
}
