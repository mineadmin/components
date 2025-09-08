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

use Carbon\Carbon;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

/**
 * Token Parser - Single Responsibility: Parse and validate JWT tokens.
 */
final readonly class TokenParser
{
    private JwtFacade $jwtFacade;

    private SignedWith $signedWith;

    public function __construct(
        private JwtConfig $config,
        private Clock $clock,
        private BlacklistManager $blacklistManager,
        private AccessTokenConstraint $accessTokenConstraint,
        private RefreshTokenConstraint $refreshTokenConstraint
    ) {
        $this->jwtFacade = new JwtFacade(clock: $this->clock);
        $this->signedWith = new SignedWith($this->config->signer, $this->config->signingKey);
    }

    /**
     * Parse access token
     * Uses pre-created constraints for better performance.
     */
    public function parseAccessToken(string $token): UnencryptedToken
    {
        return $this->jwtFacade->parse(
            $token,
            $this->signedWith,
            new StrictValidAt($this->clock, $this->createAccessTokenLeeway()),
            $this->blacklistManager->getConstraint(),
            $this->accessTokenConstraint
        );
    }

    /**
     * Parse refresh token
     * Reuses base constraints, only replaces specific constraint.
     */
    public function parseRefreshToken(string $token): UnencryptedToken
    {
        return $this->jwtFacade->parse(
            $token,
            $this->signedWith,
            new StrictValidAt($this->clock, $this->createRefreshTokenLeeway()),
            $this->blacklistManager->getConstraint(),
            $this->refreshTokenConstraint
        );
    }

    /**
     * Create access token time leeway
     * Uses configuration object, avoids runtime queries.
     */
    private function createAccessTokenLeeway(): \DateInterval
    {
        return $this->clock->now()->diff(
            Carbon::create($this->clock->now())
                ->addSeconds($this->config->ttl)
                ->toDateTimeImmutable()
        );
    }

    /**
     * Create refresh token time leeway.
     */
    private function createRefreshTokenLeeway(): \DateInterval
    {
        return $this->clock->now()->diff(
            Carbon::create($this->clock->now())
                ->addSeconds($this->config->refreshTtl)
                ->toDateTimeImmutable()
        );
    }
}
