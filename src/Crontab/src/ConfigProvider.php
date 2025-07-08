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

namespace Mine\Crontab;

use Mine\Crontab\Aspect\CrontabExecutorAspect;
use Mine\Crontab\Command\CrontabMigrateCommand;
use Mine\Crontab\Listener\CrontabProcessStarredListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listener' => [
                CrontabProcessStarredListener::class,
            ],
            'aspects' => [
                CrontabExecutorAspect::class,
            ],
            'commands' => [
                CrontabMigrateCommand::class,
            ],
        ];
    }
}
