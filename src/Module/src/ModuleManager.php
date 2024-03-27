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

namespace Mine\Module;

use Hyperf\Support\Filesystem\Filesystem;
use Mine\Module\Contract\Driver;

class ModuleManager
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Driver $driver,
        private readonly CheckModule $checkModule
    ) {}

    public function getList(): array
    {
        return Module::list();
    }

    public function add(string $name, array $config): void
    {
        Module::set($name, $config);
    }

    public function clear(): void
    {
        Module::clear();
    }

    public function remove(string $name): void
    {
        Module::remove($name);
    }

    public function scan(string $path): void
    {
        $modules = $this->filesystem->glob($path . '/*');
        foreach ($modules as $module) {
            if (! is_dir($module)) {
                continue;
            }
            $config = $this->driver->read($module);
            $this->checkModule->check($module, $config);
            Module::set($module, $config);
        }
    }
}
