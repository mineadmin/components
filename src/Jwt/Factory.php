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
use Hyperf\Contract\ConfigInterface;

use function Hyperf\Support\make;

final readonly class Factory
{
    public function __construct(
        private ConfigInterface $config,
    ) {}

    public function get(string $name = 'default'): JwtInterface
    {
        // Unified configuration parsing: eliminates special cases
        $config = JwtConfig::fromArray($this->resolveConfig($name));

        // Component-based construction: each component has clear responsibility
        $clock = make(Clock::class);
        $cacheManager = make(CacheManager::class);

        $blacklistManager = new BlacklistManager($config->blacklist, $cacheManager);

        $tokenIssuer = new TokenIssuer($config, $clock);

        $tokenParser = new TokenParser(
            $config,
            $clock,
            $blacklistManager,
            make(AccessTokenConstraint::class),
            make(RefreshTokenConstraint::class)
        );

        return new Jwt($tokenIssuer, $tokenParser, $blacklistManager);
    }

    private function resolveConfig(string $scene): array
    {
        $baseConfig = $this->config->get('jwt.default', []);
        $sceneConfig = $scene === 'default'
            ? []  // default scenario has no additional configuration
            : $this->config->get("jwt.{$scene}", []);

        return array_merge($baseConfig, $sceneConfig);
    }
}
