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

namespace Xmo\JWTAuth;

use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Xmo\JWTAuth\Util\TimeUtil;

/**
 * https://gitee.com/xmo/jwt-auth
 * 原作者 liyuzhao
 * 现维护者：xmo.
 */
class BlackList extends AbstractJWT
{
    /**
     * @var CacheInterface
     */
    public $cache;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->cache = $this->getContainer()->get(CacheInterface::class);
    }

    /**
     * 把token加入到黑名单中.
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function addTokenBlack(Plain $token, array $config = []): bool
    {
        $claims = $token->claims();
        if ($config['blacklist_enabled']) {
            $cacheKey = $this->getCacheKey($claims->get('jti'));
            $blacklistGracePeriod = 0;
            $expTime = $claims->get(RegisteredClaims::EXPIRATION_TIME);
            if (! is_numeric($expTime)) {
                $expTime = $expTime->getTimestamp();
            }
            $validUntil = TimeUtil::now()->addSeconds($blacklistGracePeriod)->getTimestamp();
            $expTime = TimeUtil::timestamp($expTime);
            $nowTime = TimeUtil::now();
            $tokenCacheTime = $expTime->max($nowTime)->diffInSeconds();
            return $this->cache->set($cacheKey, ['valid_until' => $validUntil], $tokenCacheTime);
        }
        return false;
    }

    /**
     * 判断token是否已经加入黑名单.
     * @param mixed $claims
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasTokenBlack($claims, array $config = [])
    {
        $cacheKey = $this->getCacheKey($claims['jti']);
        if ($config['blacklist_enabled'] && $config['login_type'] == 'mpop') {
            $val = $this->cache->get($cacheKey);
            return ! empty($val['valid_until']) && ! TimeUtil::isFuture($val['valid_until']);
        }

        if ($config['blacklist_enabled'] && $config['login_type'] == 'sso') {
            $val = $this->cache->get($cacheKey);
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

    /**
     * 黑名单移除token.
     * @param $key token 中的jit
     * @return bool
     * @throws InvalidArgumentException
     */
    public function remove($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * 移除所有的token缓存.
     * @return bool
     * @throws InvalidArgumentException
     */
    public function clear()
    {
        $cachePrefix = $this->getSceneConfig($this->getScene())['blacklist_prefix'];
        return $this->cache->delete("{$cachePrefix}.*");
    }

    /**
     * Get the cache time limit.
     *
     * @return int
     */
    public function getCacheTTL()
    {
        return $this->getSceneConfig($this->getScene())['ttl'];
    }

    /**
     * @return string
     */
    private function getCacheKey(string $jti)
    {
        $config = $this->getSceneConfig($this->getScene());
        return "{$config['blacklist_prefix']}_" . $jti;
    }
}
