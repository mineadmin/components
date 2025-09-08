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

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;

/**
 * Preprocesses configuration data to avoid runtime Arr::get() queries.
 */
final readonly class JwtConfig
{
    public function __construct(
        public Signer $signer,
        public Key $signingKey,
        public int $ttl,
        public int $refreshTtl,
        public BlacklistConfig $blacklist
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            signer: $config['alg'],
            signingKey: $config['key'],
            ttl: $config['ttl'] ?? 3600,
            refreshTtl: $config['refresh_ttl'] ?? 7200,
            blacklist: BlacklistConfig::fromArray($config['blacklist'] ?? [])
        );
    }
}
