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

namespace Mine\Security\Http\Tests\Cases\Jwt;

use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Security\Http\Exception\TokenValidException;
use Mine\Security\Http\Jwt\Token;
use Mine\Security\Http\Support\Jwt;
use Mine\Security\Http\Tests\Stub\UserModel;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\TokenInterface;
use Mine\SecurityBundle\Contract\UserInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 * @coversNothing
 */
class TokenTest extends TestCase
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var MockInterface
     */
    protected $jwt;

    /**
     * @var MockInterface
     */
    protected $config;

    /**
     * @var MockInterface
     */
    protected $container;

    /**
     * @var MockInterface
     */
    protected $request;

    protected function setUp(): void
    {
        $this->jwt = \Mockery::mock(Jwt::class);
        $this->config = \Mockery::mock(Config::class);
        $this->container = \Mockery::mock(ContainerInterface::class);
        $this->token = new Token($this->jwt, $this->config, $this->container);
        $this->request = \Mockery::mock(RequestInterface::class);
    }

    public function testUserWithoutRequest(): void
    {
        $this->container->allows('get')
            ->with(RequestInterface::class)
            ->andReturnNull();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request is not available.');
        $this->token->user();
    }

    public function testUserWithoutAuthorizationHeader(): void
    {
        $this->container->allows('get')
            ->with(RequestInterface::class)
            ->once()
            ->andReturn($this->request);

        $this->request->allows('hasHeader')
            ->with('Authorization')
            ->once()
            ->andReturnFalse();

        $this->expectException(TokenValidException::class);
        $this->expectExceptionMessage('Token is not available.');
        $this->token->user();
    }

    public function testUserWithValidToken(): void
    {
        $token = 'Bearer test.token';
        $scene = 'default';
        $attributes = [
            'username' => 'test_user',
            'email' => 'test@example.com',
        ];

        $this->container->allows('get')
            ->with(RequestInterface::class)
            ->andReturns($this->request);

        $this->request->allows('hasHeader')
            ->with('Authorization')
            ->andReturnTrue();

        $this->request->allows('getHeaderLine')
            ->with('Authorization')
            ->andReturns($token);

        $resolveToken = \Mockery::mock(UnencryptedToken::class);
        $resolveToken->allows('claims')
            ->once()
            ->andReturn(new DataSet($attributes, 'xxx'));

        $this->jwt->allows('parse')
            ->with('test.token', $scene)
            ->once()
            ->andReturn($resolveToken);

        $this->config->allows('get')
            ->with('entity')
            ->once()
            ->andReturn(UserModel::class);

        $user = $this->token->user();

        $this->assertInstanceOf(UserInterface::class, $user);
        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, $user->getAttribute(str_replace('__attribute__', '', $key)));
        }
    }
}
