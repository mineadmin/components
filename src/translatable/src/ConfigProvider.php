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

namespace Mine\Translatable;

use Mine\Translatable\Contracts\LocalesInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                LocalesInterface::class => Locales::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for xmo/mine-translatable.',
                    'source' => __DIR__ . '/../publish/translatable.php',
                    'destination' => BASE_PATH . '/config/autoload/translatable.php',
                ],
            ],
        ];
    }
}
