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

namespace Mine\Security\Http\Tests\Cases\Jwt\Black;

use Carbon\Carbon;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Security\Http\Jwt\Black\AbstractBlack;
use Mine\Security\Http\Support\Time;
use Mine\Security\Http\Tests\Stub\DummyBlack;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AbstractBlackTest extends TestCase
{
    protected AbstractBlack $black;

    protected function setUp(): void
    {
        $this->black = new DummyBlack();
    }

    public function testAdd(): void
    {
        $token = \Mockery::mock(UnencryptedToken::class);
        $config = [
            'blacklist_enabled' => true,
            'blacklist_prefix' => 'prefix',
        ];
        $token->allows('claims')->andReturn(new DataSet([
            'xxx' => 'xxx',
            'jti' => 'xxx',
            RegisteredClaims::EXPIRATION_TIME => Carbon::now()->toDateTimeImmutable(),
        ], RegisteredClaims::class));

        $this->assertTrue($this->black->add($token, $config));
    }

    public function testAddDisable()
    {
        $token = \Mockery::mock(UnencryptedToken::class);
        $config = [
            'blacklist_enabled' => false,
            'blacklist_prefix' => 'prefix',
        ];
        $token->allows('claims')->andReturn(new DataSet([
            'xxx' => 'xxx',
            'jti' => 'xxx',
            RegisteredClaims::EXPIRATION_TIME => Carbon::now()->toDateTimeImmutable(),
        ], RegisteredClaims::class));
        $this->assertFalse($this->black->add($token, $config));
    }

    public function testHasWithMpopLoginType(): void
    {
        $claims = [
            'jti' => '123456',
            'iat' => Time::now(),
        ];

        $config = [
            'blacklist_enabled' => true,
            'login_type' => 'mpop',
            'blacklist_prefix' => 'prefix',
        ];
        $this->assertFalse($this->black->has($claims, $config));
    }

    public function testHasWithSsoLoginType(): void
    {
        $claims = [
            'jti' => '123456',
            'iat' => Time::now(),
        ];

        $config = [
            'blacklist_enabled' => true,
            'login_type' => 'sso',
            'blacklist_prefix' => 'prefix',
        ];

        $this->assertTrue($this->black->has($claims, $config));
    }

    public function testRemove(): void
    {
        $key = '123456';
        $config = [
            'blacklist_prefix' => 'prefix',
        ];

        $this->black->remove($key, $config);
        $this->assertTrue(true);
    }

    public function testClear(): void
    {
        $config = [
            'blacklist_prefix' => 'prefix',
        ];

        $this->black->clear($config);
        $this->assertTrue(true);
    }
}
