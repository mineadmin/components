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

namespace Xmo\AppStore;

use Xmo\AppStore\Service\AppStoreService;
use Xmo\AppStore\Service\Impl\AppStoreServiceImpl;
use Xmo\AppStore\Service\Impl\PluginServiceImpl;
use Xmo\AppStore\Service\PluginService;

class ConfigProvider
{
    public function __invoke()
    {
        // Initial configuration
        $initialConfig = [
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'dependencies' => [
                AppStoreService::class => AppStoreServiceImpl::class,
                PluginService::class => PluginServiceImpl::class,
            ],
        ];

        $mineJsonPaths = Plugin::getPluginJsonPaths();
        foreach ($mineJsonPaths as $jsonPath) {
            if (file_exists($jsonPath->getPath() . '/' . Plugin::INSTALL_LOCK_FILE)) {
                $info = json_decode(file_get_contents($jsonPath->getRealPath()), true);
                if (! empty($info['composer']['config'])) {
                    $provider = (new ($info['composer']['config']))();
                    $initialConfig = array_merge_recursive($provider, $initialConfig);
                }
            }
        }
        return $initialConfig;
    }
}
