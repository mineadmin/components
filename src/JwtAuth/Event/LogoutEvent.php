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

namespace Mine\JwtAuth\Event;

/**
 * 登出事件.
 */
final class LogoutEvent
{
    public function __construct(
        private readonly object $user
    ) {}

    public function getUser(): object
    {
        return $this->user;
    }
}
