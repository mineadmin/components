<?php

declare(strict_types=1);

use Hyperf\Cache\Driver\MemoryDriver;
use Hyperf\Cache\CacheManager;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Mine\Jwt\AbstractJwt;
use Mine\Jwt\AccessTokenConstraint;
use Mine\Jwt\BlackListConstraint;
use Mine\Jwt\Clock;
use Mine\Jwt\RefreshTokenConstraint;

// 创建一个具体的 JWT 实现用于测试
class TestJwt extends AbstractJwt
{
    // 继承所有父类方法即可
}

describe('JWT Constraint Integration Test', function () {
    beforeEach(function () {
        // 模拟配置
        $this->config = [
            'alg' => new Sha256(),
            'key' => InMemory::plainText('test-key-for-integration-testing'),
            'ttl' => 3600,
            'refresh_ttl' => 7200,
            'blacklist' => [
                'enable' => false,
                'connection' => 'default',
                'ttl' => 600,
            ]
        ];

        // 创建模拟依赖
        $this->cacheManager = \Mockery::mock(CacheManager::class);
        $mockDriver = \Mockery::mock(MemoryDriver::class);
        $this->cacheManager->shouldReceive('getDriver')->andReturn($mockDriver);

        $this->clock = new Clock();
        $this->accessTokenConstraint = new AccessTokenConstraint();
        $this->refreshTokenConstraint = new RefreshTokenConstraint();

        // 创建 JWT 实例
        $this->jwt = new TestJwt(
            $this->config,
            $this->cacheManager,
            $this->clock,
            $this->accessTokenConstraint,
            $this->refreshTokenConstraint
        );
    });

    afterEach(function () {
        \Mockery::close();
    });

    it('should create and parse access token correctly', function () {
        // 创建一个 access token
        $accessToken = $this->jwt->builderAccessToken('user123');
        
        expect($accessToken->toString())->not->toBeEmpty();
        expect($accessToken->claims()->get('jti'))->toBe('user123');
        expect($accessToken->isRelatedTo('refresh'))->toBeFalse();

        // 解析 access token 应该成功
        $parsedToken = $this->jwt->parserAccessToken($accessToken->toString());
        
        expect($parsedToken->claims()->get('jti'))->toBe('user123');
        expect($parsedToken->isRelatedTo('refresh'))->toBeFalse();
    });

    it('should create and parse refresh token correctly', function () {
        // 创建一个 refresh token
        $refreshToken = $this->jwt->builderRefreshToken('user123');
        
        expect($refreshToken->toString())->not->toBeEmpty();
        expect($refreshToken->claims()->get('jti'))->toBe('user123');
        expect($refreshToken->isRelatedTo('refresh'))->toBeTrue();

        // 解析 refresh token 应该成功
        $parsedToken = $this->jwt->parserRefreshToken($refreshToken->toString());
        
        expect($parsedToken->claims()->get('jti'))->toBe('user123');
        expect($parsedToken->isRelatedTo('refresh'))->toBeTrue();
    });

    it('should fail when parsing access token as refresh token', function () {
        // 创建 access token
        $accessToken = $this->jwt->builderAccessToken('user123');
        
        // 尝试用 refresh token 解析器解析应该失败
        expect(fn() => $this->jwt->parserRefreshToken($accessToken->toString()))
            ->toThrow(\Lcobucci\JWT\Validation\RequiredConstraintsViolated::class);
    });

    it('should fail when parsing refresh token as access token', function () {
        // 创建 refresh token
        $refreshToken = $this->jwt->builderRefreshToken('user123');
        
        // 尝试用 access token 解析器解析应该失败
        expect(fn() => $this->jwt->parserAccessToken($refreshToken->toString()))
            ->toThrow(\Lcobucci\JWT\Validation\RequiredConstraintsViolated::class);
    });

    it('should maintain token type consistency', function () {
        // 测试 access token 始终不是 refresh token
        $accessToken = $this->jwt->builderAccessToken('user123');
        expect($accessToken->isRelatedTo('refresh'))->toBeFalse();

        // 测试 refresh token 始终是 refresh token
        $refreshToken = $this->jwt->builderRefreshToken('user123');
        expect($refreshToken->isRelatedTo('refresh'))->toBeTrue();
    });

    it('should handle custom claims in tokens correctly', function () {
        // 测试带自定义声明的 access token
        $accessToken = $this->jwt->builderAccessToken('user123', function ($builder) {
            return $builder->withClaim('role', 'admin')
                          ->withClaim('permissions', ['read', 'write']);
        });

        $parsedAccessToken = $this->jwt->parserAccessToken($accessToken->toString());
        expect($parsedAccessToken->claims()->get('role'))->toBe('admin');
        expect($parsedAccessToken->claims()->get('permissions'))->toBe(['read', 'write']);
        expect($parsedAccessToken->isRelatedTo('refresh'))->toBeFalse();

        // 测试带自定义声明的 refresh token
        $refreshToken = $this->jwt->builderRefreshToken('user123', function ($builder) {
            return $builder->withClaim('client_id', 'mobile_app');
        });

        $parsedRefreshToken = $this->jwt->parserRefreshToken($refreshToken->toString());
        expect($parsedRefreshToken->claims()->get('client_id'))->toBe('mobile_app');
        expect($parsedRefreshToken->isRelatedTo('refresh'))->toBeTrue();
    });

    it('should validate token expiration correctly', function () {
        // 这个测试确保时间验证仍然有效
        $accessToken = $this->jwt->builderAccessToken('user123');
        
        // 刚创建的 token 应该有效 - 不应该抛出任何异常
        $result = $this->jwt->parserAccessToken($accessToken->toString());
        expect($result)->toBeObject();
    });
});