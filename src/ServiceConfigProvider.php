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

use Mine\Annotation\ComponentCollector;
use Mine\Annotation\CrudModelCollector;
use Mine\Annotation\DependProxyCollector;
use Mine\Command\MineGenServiceCommand;
use Mine\Listener\DependProxyListener;

class ServiceConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
                MineGenServiceCommand::class,
            ],
            'listeners' => [
                DependProxyListener::class => PHP_INT_MAX,
            ],
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        DependProxyCollector::class,
                        CrudModelCollector::class,
                        ComponentCollector::class,
                    ],
                ],
            ],
        ];
    }
}
