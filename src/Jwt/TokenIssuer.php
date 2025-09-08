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
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\UnencryptedToken;

final readonly class TokenIssuer
{
    private JwtFacade $jwtFacade;

    public function __construct(
        private JwtConfig $config,
        private Clock $clock
    ) {
        $this->jwtFacade = new JwtFacade(clock: $this->clock);
    }

    public function issueAccessToken(string $sub, ?\Closure $callable = null): UnencryptedToken
    {
        return $this->jwtFacade->issue(
            $this->config->signer,
            $this->config->signingKey,
            fn (Builder $builder, \DateTimeImmutable $issuedAt) => $this->buildAccessToken($builder, $issuedAt, $sub, $callable)
        );
    }

    public function issueRefreshToken(string $sub, ?\Closure $callable = null): UnencryptedToken
    {
        return $this->jwtFacade->issue(
            $this->config->signer,
            $this->config->signingKey,
            fn (Builder $builder, \DateTimeImmutable $issuedAt) => $this->buildRefreshToken($builder, $issuedAt, $sub, $callable)
        );
    }

    private function buildAccessToken(
        Builder $builder,
        \DateTimeImmutable $issuedAt,
        string $sub,
        ?\Closure $callable
    ): Builder {
        $builder = $builder
            ->identifiedBy($sub)
            ->expiresAt($this->getAccessExpireAt($issuedAt));

        return $callable ? $callable($builder) : $builder;
    }

    private function buildRefreshToken(
        Builder $builder,
        \DateTimeImmutable $issuedAt,
        string $sub,
        ?\Closure $callable
    ): Builder {
        $builder = $builder
            ->identifiedBy($sub)
            ->expiresAt($this->getRefreshExpireAt($issuedAt))
            ->relatedTo('refresh'); // Key difference: mark as refresh token

        return $callable ? $callable($builder) : $builder;
    }

    private function getAccessExpireAt(\DateTimeImmutable $immutable): \DateTimeImmutable
    {
        return Carbon::create($immutable)
            ->addSeconds($this->config->ttl)
            ->toDateTimeImmutable();
    }

    private function getRefreshExpireAt(\DateTimeImmutable $immutable): \DateTimeImmutable
    {
        return Carbon::create($immutable)
            ->addSeconds($this->config->refreshTtl)
            ->toDateTimeImmutable();
    }
}
