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
use Hyperf\Database\Model\Model;
use Mine\NextCoreX\Channel\RedisChannel;
use Mine\NextCoreX\Protocols\PhpSerialize;

return [
    // 驱动类型
    'driver' => RedisChannel::class,
    'redis' => [
        'queue' => 'queue:%s',
    ],
    'orm' => [
        'mode' => 'db',
        'table' => 'queue',
        'model' => Model::class,
        'listening' => [
            'interval' => 1000,
        ],
    ],
    'serialize' => PhpSerialize::class,
];
