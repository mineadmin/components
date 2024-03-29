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

namespace Mine\Security\Access;

use FriendsOfHyperf\Facade\Facade;

/**
 * @mixin Rbac
 */
class RbacFacade extends Facade
{
    public static function getFacadeRoot(): string
    {
        return Rbac::class;
    }
}
