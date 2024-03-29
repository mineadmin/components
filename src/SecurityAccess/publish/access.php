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
use Casbin\Enforcer;

return [
    'default' => 'rbac',
    'component' => [
        'rbac' => [
            'construct' => [
                __DIR__ . '/rbac_model.conf',
                __DIR__ . '/rbac_policy.csv',
            ],
            'enforcer' => Enforcer::class,
        ],
    ],
];
