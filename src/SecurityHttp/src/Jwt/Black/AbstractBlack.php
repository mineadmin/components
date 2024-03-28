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

use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Security\Http\Contract\BlackContract;
use Mine\Security\Http\Support\Time;

abstract class AbstractBlack implements BlackContract
{
    public function add(UnencryptedToken $token, array $config = []): bool
    {
        $claims = $token->claims();
        if ($config['blacklist_enabled']) {
            $cacheKey = $this->getCacheKey($claims->get('jti'), $config);
            $blacklistGracePeriod = 0;
            $expTime = $claims->get(RegisteredClaims::EXPIRATION_TIME);
            if (! is_numeric($expTime)) {
                $expTime = $expTime->getTimestamp();
            }
            $validUntil = Time::now()->addSeconds($blacklistGracePeriod)->getTimestamp();
            $expTime = Time::timestamp($expTime);
            $nowTime = Time::now();
            $tokenCacheTime = $expTime->max($nowTime)->diffInSeconds();
            return $this->storageAdd($cacheKey, ['valid_until' => $validUntil], $tokenCacheTime, $config['blacklist_prefix']);
        }
        return false;
    }

    abstract public function storageAdd(string $cacheKey, array $val, int $tokenCacheTime, string $prefix): bool;

    abstract public function storageGet(string $cacheKey, string $prefix): mixed;

    abstract public function storageDelete(string $cacheKey, string $prefix): bool;

    abstract public function storageClear(string $prefix): bool;

    /**
     * Determine if the token has been blacklisted.
     * @param mixed $claims
     */
    public function has($claims, array $config = []): bool
    {
        $cacheKey = $this->getCacheKey($claims['jti'], $config);
        if ($config['blacklist_enabled'] && $config['login_type'] === 'mpop') {
            $val = $this->storageGet($cacheKey, $config['blacklist_prefix']);
            return ! empty($val['valid_until']) && ! Time::isFuture($val['valid_until']);
        }

        if ($config['blacklist_enabled'] && $config['login_type'] === 'sso') {
            $val = $this->storageGet($cacheKey, $config['blacklist_prefix']);
            // 这里为什么要大于等于0，因为在刷新token时，缓存时间跟签发时间可能一致，详细请看刷新token方法
            if (! is_null($claims['iat']) && ! empty($val['valid_until'])) {
                $isFuture = ($claims['iat']->getTimestamp() - $val['valid_until']) >= 0;
            } else {
                $isFuture = false;
            }
            // check whether the expiry + grace has past
            return ! $isFuture;
        }
        return false;
    }

    public function remove($key, array $config = []): void
    {
        $this->storageDelete($key, $config['blacklist_prefix']);
    }

    public function clear(array $config = []): void
    {
        $this->storageClear($config['blacklist_prefix']);
    }

    private function getCacheKey(string $jti, array $config = []): string
    {
        return sprintf('%s_%s', $config['blacklist_prefix'], $jti);
    }
}
