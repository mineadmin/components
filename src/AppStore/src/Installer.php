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

namespace Mine\AppStore;

class Installer
{
    public static function postAutoloadDump($event): void
    {
        if (! file_exists(Plugin::PLUGIN_PATH)) {
            if (! mkdir($concurrentDirectory = Plugin::PLUGIN_PATH, 0755, true) && ! is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $binFile = file_get_contents(BASE_PATH . '/bin/hyperf.php');
            if (str_contains($binFile, 'Mine\AppStore\Plugin::init();')) {
                $binFile = str_replace('Hyperf\Di\ClassLoader::init();', '\\Mine\\AppStore\\Plugin::init();
    Hyperf\\Di\\ClassLoader::init();', $binFile);
                file_put_contents(BASE_PATH . '/bin/hyperf.php', $binFile);
                $event->getIO()->write('Plugin initialization code added successfully.');
            }
            $event->getIO()->write('Plugin directory created successfully');
            return;
        }
    }
}
