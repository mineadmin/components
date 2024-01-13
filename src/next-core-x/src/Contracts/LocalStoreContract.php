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

namespace Mine\NextCoreX\Contracts;

interface LocalStoreContract
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): bool;

    public function delete(string $key): bool;
}
