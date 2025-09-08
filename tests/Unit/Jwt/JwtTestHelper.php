<?php

declare(strict_types=1);

namespace Mine\Tests\Unit\Jwt;

use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use DateTimeImmutable;

/**
 * JWT测试辅助类
 * 
 * 专门用于在测试中创建和管理JWT token
 * 遵循单一职责原则，只负责token的创建逻辑
 */
class JwtTestHelper
{
    private Sha256 $signer;
    private InMemory $key;

    public function __construct()
    {
        $this->signer = new Sha256();
        $this->key = InMemory::plainText('test-key-for-jwt-constraint-testing');
    }

    /**
     * 创建测试用的JWT token
     *
     * @param \Closure|null $modifier 可选的修改器函数，用于自定义token属性
     * @return Token 构建好的JWT token
     */
    public function createToken(?\Closure $modifier = null): Token
    {
        $builder = new \Lcobucci\JWT\Token\Builder(
            new JoseEncoder(),
            ChainedFormatter::default()
        );

        $builder = $builder->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->modify('+1 hour'))
            ->identifiedBy('test-token');

        if ($modifier) {
            $builder = $modifier($builder);
        }

        return $builder->getToken($this->signer, $this->key);
    }
}