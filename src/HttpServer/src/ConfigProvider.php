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

namespace Mine\HttpServer;

use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Listener\BootApplicationListener;
use Mine\HttpServer\Log\RequestIdGenerator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 默认 Command 的定义，合并到 Hyperf\Contract\ConfigInterface 内，换个方式理解也就是与 config/autoload/commands.php 对应
            'commands' => [],
            // 与 commands 类似
            'listeners' => [
                BootApplicationListener::class,
            ],
            // 合并到  config/autoload/dependencies.php 文件
            'dependencies' => [
                RequestIdGeneratorInterface::class => RequestIdGenerator::class,
            ],
            'publish' => [
                [
                    'id' => 'MineAdmin-HttpServer-Trans',
                    'description' => 'MineAdmin Response Code Translation File',
                    'source' => __DIR__ . '/../publish/languages/en/result.php',
                    'destination' => BASE_PATH . '/storage/languages/en/result.php',
                ],
                [
                    'id' => 'MineAdmin-HttpServer-Trans zh_CN',
                    'description' => 'MineAdmin Response Code Translation File',
                    'source' => __DIR__ . '/../publish/languages/zh_CN/result.php',
                    'destination' => BASE_PATH . '/storage/languages/zh_CN/result.php',
                ],
            ],
        ];
    }
}
