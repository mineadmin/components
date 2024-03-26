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

namespace Mine\Module\Contract;

use Mine\Module\Exception\ModuleConfigNotFoundException;

interface Driver
{
    public function read(string $path): array;

    public function write(string $path, array $data): void;

    /**
     * @throws ModuleConfigNotFoundException
     */
    public function notFound(string $message): void;
}
