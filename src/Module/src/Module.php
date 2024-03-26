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

namespace Mine\Module;

use Hyperf\Support\Traits\Container;

class Module
{
    use Container;

    public static function remove(string $id): void
    {
        unset(static::$container[$id]);
    }
}
