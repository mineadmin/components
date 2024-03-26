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

use Hyperf\Crontab\Crontab;
use Hyperf\DbConnection\Db;

class Schedule
{
    public const CRONTAB_TABLE = 'crontab';

    /**
     * @return Crontab[]
     */
    public function getCrontab(): array
    {
        $list = [];
        $crontabList = Db::table(self::CRONTAB_TABLE)->where('status', 1)->get();
        if ($crontabList->count() === 0) {
            return [];
        }
        foreach ($crontabList as $crontab) {
            $list[] = new \Mine\Crontab\Crontab($crontab->id);
        }
        return $list;
    }
}
