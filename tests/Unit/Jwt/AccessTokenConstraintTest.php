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
use Mine\Jwt\AccessTokenConstraint;
use Mine\Tests\Unit\Jwt\JwtTestHelper;

describe('AccessTokenConstraint', function () {
    beforeEach(function () {
        $this->constraint = new AccessTokenConstraint();
        $this->helper = new JwtTestHelper();
    });

    it('should pass validation for access tokens (non-refresh tokens)', function () {
        // 创建一个普通的 access token（不设置 relatedTo 'refresh'）
        $token = $this->helper->createToken(static function ($builder) {
            return $builder->relatedTo('user123');  // 不是 'refresh'
        });

        // 应该不抛出异常
        expect(fn () => $this->constraint->assert($token))->not->toThrow(ConstraintViolation::class);
    });

    it('should pass validation for tokens without relatedTo claim', function () {
        // 创建一个没有 relatedTo 声明的 token
        $token = $this->helper->createToken();

        // 应该不抛出异常
        expect(fn () => $this->constraint->assert($token))->not->toThrow(ConstraintViolation::class);
    });

    it('should fail validation for refresh tokens', function () {
        // 创建一个 refresh token
        $token = $this->helper->createToken(static function ($builder) {
            return $builder->relatedTo('refresh');
        });

        // 应该抛出 ConstraintViolation 异常
        expect(fn () => $this->constraint->assert($token))
            ->toThrow(ConstraintViolation::class, 'Access token cannot be a refresh token');
    });

    it('should fail validation for any token related to refresh', function () {
        // 测试其他可能包含 'refresh' 的情况
        $token = $this->helper->createToken(static function ($builder) {
            return $builder->relatedTo('refresh');
        });

        expect(fn () => $this->constraint->assert($token))
            ->toThrow(ConstraintViolation::class);
    });
});
