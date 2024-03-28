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

namespace Mine\Security\Http\Jwt\Black;

use Psr\SimpleCache\CacheInterface;

class CacheBlack extends AbstractBlack
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {}

    public function storageAdd(string $cacheKey, array $val, int $tokenCacheTime, string $prefix): bool
    {
        return $this->cache->set($prefix . ':' . $cacheKey, $val, $tokenCacheTime);
    }

    public function storageGet(string $cacheKey, string $prefix): mixed
    {
        return $this->cache->get($prefix . ':' . $cacheKey);
    }

    public function storageDelete(string $cacheKey, string $prefix): bool
    {
        return $this->cache->delete($prefix . ':' . $cacheKey);
    }

    public function storageClear(string $prefix): bool
    {
        return $this->cache->delete($prefix . ':*');
    }
}
