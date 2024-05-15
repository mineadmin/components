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

namespace Mine\Admin\Security;

use Mine\SecurityBundle\Config as Base;

class Config extends Base
{
    public const PREFIX = 'admin.security';

    public function get(string $key, mixed $default = null): mixed
    {
        return parent::get($key, $default); // TODO: Change the autogenerated stub
    }
}
