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

namespace Mine\SecurityBundle\Event;

use Mine\SecurityBundle\Contract\UserInterface;

class Verified
{
    public function __construct(
        private readonly UserInterface $user
    ) {}

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
