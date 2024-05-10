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

namespace Mine\AppStore\Utils;

use Nette\Utils\FileSystem;

final class FileSystemUtils
{
    public const BACK = '.back';

    /**
     * Copies the file to the specified path.
     * If the third parameter is specified,
     * it will determine if the target file exists.
     * If it exists, it will be renamed to .back.
     * and then copied again.
     */
    public static function copy(string $source, string $dist, bool $back = true): void
    {
        if (! file_exists($source)) {
            throw new \RuntimeException(sprintf('%s file does not exist', $source));
        }
        if (file_exists($dist) && $back) {
            FileSystem::copy($dist, $dist . self::BACK);
        }
        FileSystem::copy($source, $dist);
    }

    public static function recovery(string $relationFilePath, string $dist): void
    {
        $targetFile = $dist . '/' . $relationFilePath;
        $backFile = $targetFile . self::BACK;
        if (file_exists($backFile)) {
            FileSystem::copy($backFile, $targetFile);
        }
    }

    /**
     * Checks if the given name is a valid directory path.
     */
    public static function checkDirectory(string $name): bool
    {
        return (bool) preg_match('/^\/(?:[^\/\0]+\/)*[^\/\0]+$/', $name);
    }
}
