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

class JsonDriver implements Driver
{
    use DriverTrait;

    public const CONFIG_FILE = '/config.json';

    public function read(string $path): array
    {
        if (! file_exists($path . self::CONFIG_FILE)) {
            $this->notFound(sprintf('%s configuration file not found in module %s', $path, self::CONFIG_FILE));
        }
        return json_decode(file_get_contents($path . self::CONFIG_FILE), true, 512, JSON_THROW_ON_ERROR);
    }

    public function write(string $path, array $data): void
    {
        file_put_contents($path . self::CONFIG_FILE, json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
