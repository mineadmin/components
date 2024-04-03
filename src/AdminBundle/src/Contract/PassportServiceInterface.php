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

namespace Mine\Admin\Bundle\Contract;

use Mine\Admin\Bundle\Dto\UserLoginDto;
use Mine\SecurityBundle\Contract\UserInterface;

interface PassportServiceInterface
{
    public function login(UserLoginDto $userLoginDto): ?UserInterface;

    public function logout(?string $identifier = null): void;
}
