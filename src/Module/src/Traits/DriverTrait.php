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

namespace Mine\Module\Traits;

use Mine\Module\Exception\ModuleConfigNotFoundException;

trait DriverTrait
{
    public function notFound(string $message): void
    {
        throw new ModuleConfigNotFoundException($message);
    }
}
