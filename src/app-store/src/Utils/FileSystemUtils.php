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

namespace Xmo\AppStore\Utils;

class FileSystemUtils
{
    public static function copyDirectory($source, $destination): void
    {
        if (! file_exists($destination)) {
            if (! mkdir($destination, 0777, true) && ! is_dir($destination)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination));
            }
        }

        if (is_dir($source)) {
            $handle = opendir($source);

            if ($handle) {
                while (($entry = readdir($handle)) !== false) {
                    if ($entry !== '.' && $entry !== '..') {
                        $src = $source . '/' . $entry;
                        $dst = $destination . '/' . $entry;

                        if (is_dir($src)) {
                            self::copyDirectory($src, $dst);
                        } else {
                            copy($src, $dst);
                        }
                    }
                }

                closedir($handle);
            }
        } elseif (is_file($source)) {
            copy($source, $destination);
        }
    }
}
