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

use Casbin\Enforcer;
use Mine\Security\Access\Contract\Access;

/**
 * @mixin Enforcer
 */
class Rbac
{
    public function __construct(
        private readonly Access $access
    ) {}

    public function __call($name, $arguments)
    {
        return $this->getAccess()->get('rbac')->{$name}(...$arguments);
    }

    public function getAccess(): Access
    {
        return $this->access;
    }
}
