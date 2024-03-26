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

class PhpDriver implements Driver
{
    use DriverTrait;

    public const MODULE_FILE = '/config.php';

    public function read(string $path): array
    {
        $file = $path . self::MODULE_FILE;
        if (! file_exists($file)) {
            $this->notFound(sprintf('%s configuration file not found in module path %s', $path, self::MODULE_FILE));
        }
        return require $file;
    }

    public function write(string $path, array $data): void
    {
        file_put_contents($path . self::MODULE_FILE, '<?php return ' . var_export($data, true) . ';');
    }
}
