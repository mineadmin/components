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
use Symfony\Component\Yaml\Yaml;

class YamlDriver implements Driver
{
    use DriverTrait;

    public const CONFIG_FILE = '/config.yaml';

    public function read(string $path): array
    {
        if (! file_exists($path . self::CONFIG_FILE)) {
            $this->notFound(sprintf('Configuration file not found in module %s %s', $path, self::CONFIG_FILE));
        }
        return Yaml::parseFile($path . self::CONFIG_FILE);
    }

    public function write(string $path, array $data): void
    {
        file_put_contents($path . self::CONFIG_FILE, Yaml::dump($data), LOCK_EX);
    }
}
