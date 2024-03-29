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

namespace Mine\Security\Http;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'id' => 'security-http',
                    'description' => 'Security http configure',
                    'source' => dirname(__DIR__) . '/publish/security.php',
                    'destination' => BASE_PATH . '/config/autoload/security.php',
                ],
            ],
        ];
    }
}
