<?php

declare(strict_types=1);

/**
 *
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo <root@imoi.cn>
 * @Link   https://www.mineadmin.com/
 * @Github  https://github.com/kanyxmo
 * @Document https://doc.mineadmin.com/
 *
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
                ]
            ],
        ];
    }
}
