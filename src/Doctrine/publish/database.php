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
use Hyperf\Pool\Event\ReleaseConnection;

use function Hyperf\Support\env;

return [
    'paths' => [
        BASE_PATH . '/app/Entity',
    ],
    'isDevMode' => (bool) env('APP_DEBUG'),
    'cache' => 'default',
    'proxy_dir' => BASE_PATH . '/runtime/container/doctrine/proxies',
    'database' => [
        'default' => [
            'driver' => 'pdo_sqlite',
            'path' => BASE_PATH . '/db.sqlite',
            'option' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'maxIdleTime' => 60,
                'events' => [
                    ReleaseConnection::class,
                ],
            ],
        ],
    ],
];
