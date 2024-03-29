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

namespace Mine\Security\Http\Tests\Stub;

use Mine\Security\Http\Jwt\Black\AbstractBlack;

class DummyBlack extends AbstractBlack
{
    public function storageAdd(string $cacheKey, array $val, int $tokenCacheTime, string $prefix): bool
    {
        // Dummy implementation
        return true;
    }

    public function storageGet(string $cacheKey, string $prefix): mixed
    {
        // Dummy implementation
        return [];
    }

    public function storageDelete(string $cacheKey, string $prefix): bool
    {
        // Dummy implementation
        return true;
    }

    public function storageClear(string $prefix): bool
    {
        // Dummy implementation
        return true;
    }
}
