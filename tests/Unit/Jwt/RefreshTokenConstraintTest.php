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
use Lcobucci\JWT\Validation\ConstraintViolation;
use Mine\Jwt\RefreshTokenConstraint;
use Mine\Tests\Unit\Jwt\JwtTestHelper;

describe('RefreshTokenConstraint', function () {
    beforeEach(function () {
        $this->constraint = new RefreshTokenConstraint();
        $this->helper = new JwtTestHelper();
    });

    it('should pass validation for refresh tokens', function () {
        // 创建一个 refresh token
        $token = $this->helper->createToken(function ($builder) {
            return $builder->relatedTo('refresh');
        });

        // 应该不抛出异常
        expect(fn () => $this->constraint->assert($token))->not->toThrow(ConstraintViolation::class);
    });

    it('should fail validation for access tokens (non-refresh tokens)', function () {
        // 创建一个普通的 access token（不设置 relatedTo 'refresh'）
        $token = $this->helper->createToken(function ($builder) {
            return $builder->relatedTo('user123');  // 不是 'refresh'
        });

        // 应该抛出 ConstraintViolation 异常
        expect(fn () => $this->constraint->assert($token))
            ->toThrow(ConstraintViolation::class, 'Token must be a refresh token');
    });

    it('should fail validation for tokens without relatedTo claim', function () {
        // 创建一个没有 relatedTo 声明的 token
        $token = $this->helper->createToken();

        // 应该抛出异常，因为没有 relatedTo('refresh')
        expect(fn () => $this->constraint->assert($token))
            ->toThrow(ConstraintViolation::class, 'Token must be a refresh token');
    });

    it('should fail validation for tokens with different relatedTo values', function () {
        // 测试各种不同的 relatedTo 值
        $testValues = ['access', 'admin', 'user', '', 'refresh-token'];

        foreach ($testValues as $value) {
            $token = $this->helper->createToken(function ($builder) use ($value) {
                return $builder->relatedTo($value);
            });

            expect(fn () => $this->constraint->assert($token))
                ->toThrow(ConstraintViolation::class, 'Token must be a refresh token');
        }
    });

    it('should only accept tokens with exact refresh relatedTo value', function () {
        // 测试只有精确的 'refresh' 值才会通过
        $token = $this->helper->createToken(function ($builder) {
            return $builder->relatedTo('refresh');
        });

        expect(fn () => $this->constraint->assert($token))->not->toThrow(ConstraintViolation::class);

        // 但 'REFRESH' 或 'refresh ' 不应该通过
        $upperCaseToken = $this->helper->createToken(function ($builder) {
            return $builder->relatedTo('REFRESH');
        });

        expect(fn () => $this->constraint->assert($upperCaseToken))
            ->toThrow(ConstraintViolation::class);
    });
});
