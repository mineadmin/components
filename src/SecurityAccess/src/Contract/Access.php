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

namespace Mine\Security\Access\Contract;

use Casbin\Enforcer;
use Mine\Security\Access\Exception\AccessException;

interface Access
{
    /**
     * @throws AccessException
     */
    public function get(?string $name = null): Enforcer;
}
