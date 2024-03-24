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

namespace Mine\HttpServer;

use Hyperf\Context\ApplicationContext;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;

class RequestIdHolder
{
    public static function getId(): string
    {
        return ApplicationContext::getContainer()
            ->get(RequestIdGeneratorInterface::class)->generate();
    }
}
