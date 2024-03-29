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

namespace Mine\Security\Access;

use Mine\Security\Access\Contract\Access;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Access::class => Manager::class,
            ],
            'publish' => [
                [
                    'id' => 'access rbac conf',
                    'description' => 'Access Rbac Conf',
                    'source' => __DIR__ . '/../publish/access.php',
                    'destination' => BASE_PATH . '/config/autoload/access.php',
                ],
                [
                    'id' => 'access rbac model conf',
                    'description' => 'Access Rbac Model Conf',
                    'source' => __DIR__ . '/../publish/rbac_model.conf',
                    'destination' => BASE_PATH . '/config/autoload/rbac_model.conf',
                ],
                [
                    'id' => 'access rbac policy csv',
                    'description' => 'Access Rbac Policy csv',
                    'source' => __DIR__ . '/../publish/rbac_policy.csv',
                    'destination' => BASE_PATH . '/config/autoload/rbac_policy.csv',
                ],
            ],
        ];
    }
}
