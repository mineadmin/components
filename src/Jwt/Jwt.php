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

use Lcobucci\JWT\UnencryptedToken;

final readonly class Jwt implements JwtInterface
{
    public function __construct(
        private TokenIssuer $issuer,
        private TokenParser $parser,
        private BlacklistManager $blacklistManager
    ) {}

    /**
     * Build access token - delegate to TokenIssuer.
     */
    public function builderAccessToken(string $sub, ?\Closure $callable = null): UnencryptedToken
    {
        return $this->issuer->issueAccessToken($sub, $callable);
    }

    /**
     * Build refresh token - delegate to TokenIssuer.
     */
    public function builderRefreshToken(string $sub, ?\Closure $callable = null): UnencryptedToken
    {
        return $this->issuer->issueRefreshToken($sub, $callable);
    }

    /**
     * Parse access token - delegate to TokenParser.
     */
    public function parserAccessToken(string $accessToken): UnencryptedToken
    {
        return $this->parser->parseAccessToken($accessToken);
    }

    /**
     * Parse refresh token - delegate to TokenParser.
     */
    public function parserRefreshToken(string $refreshToken): UnencryptedToken
    {
        return $this->parser->parseRefreshToken($refreshToken);
    }

    /**
     * Add to blacklist - delegate to BlacklistManager.
     */
    public function addBlackList(UnencryptedToken $token): bool
    {
        return $this->blacklistManager->add($token);
    }

    /**
     * Check blacklist - delegate to BlacklistManager.
     */
    public function hasBlackList(UnencryptedToken $token): bool
    {
        return $this->blacklistManager->has($token);
    }

    /**
     * Remove from blacklist - delegate to BlacklistManager.
     */
    public function removeBlackList(UnencryptedToken $token): bool
    {
        return $this->blacklistManager->remove($token);
    }

}
