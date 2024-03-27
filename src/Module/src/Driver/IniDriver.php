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

namespace Mine\Module\Driver;

use Mine\Module\Contract\Driver;
use Mine\Module\Traits\DriverTrait;

class IniDriver implements Driver
{
    use DriverTrait;

    public const CONFIG_FILE = '/config.ini';

    public function read(string $path): array
    {
        if (! file_exists($path . self::CONFIG_FILE)) {
            $this->notFound(sprintf('%s Config file %s not found', $path, self::CONFIG_FILE));
        }
        return parse_ini_file($path . self::CONFIG_FILE);
    }

    public function write(string $path, array $data): void
    {
        $ini = '';
        foreach ($data as $key => $value) {
            $ini .= $key . '=' . $value . PHP_EOL;
        }
        file_put_contents($path . self::CONFIG_FILE, $ini);
    }
}
