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

namespace Mine;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合并到  config/autoload/mineadmin.php 文件
            'mineadmin' => [
                // 应用中心配置（只有在开发模式下可以使用应用中心）
                'appstore_config' => [
                    // 是否开启应用中心
                    'enabled' => true,
                    // 前端vue所在目录，默认后端根目录的 ./web 下
                    'web_path' => BASE_PATH . '/web',
                ],
            ],
        ];
    }
}
