<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/translatable.
 *
 * @link     https://github.com/hyperf-ext/translatable
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/translatable/blob/master/LICENSE
 */
namespace HyperfExt\Translatable;

use HyperfExt\Translatable\Contracts\LocalesInterface;
use HyperfExt\Translatable\Listeners\ModelDeletingListener;
use HyperfExt\Translatable\Listeners\ModelSavedListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                LocalesInterface::class => Locales::class,
            ],
            'listeners' => [
                ModelSavedListener::class,
                ModelDeletingListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for hyperf-ext/translatable.',
                    'source' => __DIR__ . '/../publish/translatable.php',
                    'destination' => BASE_PATH . '/config/autoload/translatable.php',
                ],
            ],
        ];
    }
}
