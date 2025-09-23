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

namespace Mine\Jwt;

use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;

final readonly class BlacklistManager
{
    private DriverInterface $cacheDriver;

    public function __construct(
        private BlacklistConfig $config,
        private CacheManager $cacheManager
    ) {
        // Get cache driver during construction, avoid repeated acquisition
        $this->cacheDriver = $this->cacheManager->getDriver($this->config->connection);
    }

    /**
     * Add token to blacklist.
     */
    public function add(UnencryptedToken $token): bool
    {
        if (! $this->config->enable) {
            return true; // Return success when blacklist is disabled
        }

        return $this->cacheDriver->set(
            $token->toString(),
            1,
            $this->config->ttl
        );
    }

    /**
     * Check if token is in blacklist.
     */
    public function has(UnencryptedToken $token): bool
    {
        return $this->config->enable && $this->cacheDriver->has($token->toString());
    }

    /**
     * Remove token from blacklist.
     */
    public function remove(UnencryptedToken $token): bool
    {
        if (! $this->config->enable) {
            return true; // Return success when blacklist is disabled
        }

        return $this->cacheDriver->delete($token->toString());
    }

    /**
     * Get blacklist constraint object.
     */
    public function getConstraint(): Constraint
    {
        return new BlackListConstraint($this->config->enable, $this->cacheDriver);
    }

    /**
     * Check if blacklist is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config->enable;
    }
}
