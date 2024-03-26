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

namespace Mine\Event;

class UserLoginBefore
{
    public array $inputData;

    public function __construct(array $inputData)
    {
        $this->inputData = $inputData;
    }
}
