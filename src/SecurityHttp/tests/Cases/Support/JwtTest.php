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

namespace Mine\Security\Http\Tests\Cases\Support;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Security\Http\Contract\BlackContract;
use Mine\Security\Http\Exception\JwtConfigException;
use Mine\Security\Http\Exception\TokenValidException;
use Mine\Security\Http\Jwt\Black\CacheBlack;
use Mine\Security\Http\Support\Jwt;
use Mine\Security\Http\TokenObject;
use Mine\SecurityBundle\Config;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class JwtTest extends TestCase
{
    private Jwt $jwt;

    private ContainerInterface $container;

    private Config $config;

    protected function setUp(): void
    {
        $this->container = \Mockery::mock(ContainerInterface::class);
        $this->jwt = new Jwt(
            $this->config = \Mockery::mock(Config::class),
            $this->container
        );
        $stubConfig = require dirname(__DIR__, 3) . '/publish/security.php';
        $jwtConfig = $stubConfig['jwt'];
        $jwtConfig['secret'] = base64_encode(random_bytes(32));
        $jwtConfig['black'] = CacheBlack::class;
        $jwtConfig['scene']['sso'] = [
            'secret' => base64_encode(random_bytes(32)),
            'login_type' => 'sso',
        ];
        $this->config->allows('get')
            ->with('jwt', [])
            ->andReturn($jwtConfig);
    }

    public function testGenerator(): void
    {
        $tokenObject = new TokenObject();
        $tokenObject->setIssuedBy('xxxx');
        $tokenObject->setClaims(['foo' => 'bar']);
        $token = $this->jwt->generator($tokenObject);
        $this->assertInstanceOf(UnencryptedToken::class, $token);
    }

    public function testGeneratorWithSsoKey(): void
    {
        $black = \Mockery::mock(BlackContract::class);
        $config = $this->jwt->getSceneConfig('sso');
        $signer = $this->jwt->getSigner($config);
        $key = $this->jwt->getKey($config);
        $this->container->allows('get')->with(CacheBlack::class)->andReturn($black);

        $black->allows('add')->andReturn(true);
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 111]);
        $token = $this->jwt->generator($tokenObject, 'sso');
        $this->assertInstanceOf(UnencryptedToken::class, $token);
        $this->assertTrue($this->jwt->getValidationData($signer, $key, $token->toString()));
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 111]);
        $newToken = $this->jwt->generator($tokenObject, 'sso');
        $black->allows('has')->andReturnUsing(function ($key, $data) {
            return $key['jti'] === 'sso_111';
        });
        try {
            $this->jwt->parse($token->toString(), 'sso', true);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Token authentication does not pass');
        }
        $this->expectException(JwtConfigException::class);
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar']);
        $this->jwt->generator($tokenObject, 'sso');
    }

    public function testGeneratorWithUnique(): void
    {
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['foo' => 'bar']);
        $token = $this->jwt->generator($tokenObject);
        $this->assertInstanceOf(UnencryptedToken::class, $token);
    }

    public function testParse(): void
    {
        $black = \Mockery::mock(BlackContract::class);
        $this->container->allows('get')->andReturn(CacheBlack::class)->andReturn($black);
        $black->allows('has')->andReturn(false);
        $object = new TokenObject();
        $object->setIssuedBy('xxxx');
        $object->setClaims(['foo' => 'bar', 'uid' => 'xxx']);
        $token = $this->jwt->generator($object);
        $parsedToken = $this->jwt->parse($token->toString());
        $this->assertInstanceOf(Token::class, $parsedToken);
    }

    public function testParseWithSsoKey(): void
    {
        $black = \Mockery::mock(BlackContract::class);
        $this->container->allows('get')->andReturn(CacheBlack::class)->andReturn($black);
        $black->allows('has')->andReturn(false);
        $black->allows('add')->andReturn(true);
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 'xxx']);
        $token = $this->jwt->generator($tokenObject, 'sso');
        $parsedToken = $this->jwt->parse($token->toString(), 'sso');
        $this->assertInstanceOf(Token::class, $parsedToken);
    }

    public function testParseWithIndependentTokenVerify(): void
    {
        $black = \Mockery::mock(BlackContract::class);
        $this->container->allows('get')->andReturn(CacheBlack::class)->andReturn($black);
        $black->allows('has')->andReturn(false);
        $black->allows('add')->andReturn(true);
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 'xxx']);
        $token = $this->jwt->generator($tokenObject, 'sso');
        $jwt = new Jwt($this->config, $this->container);
        $parsedToken = $jwt->parse($token->toString());
        $this->assertInstanceOf(Token::class, $parsedToken);
    }

    public function testParseWithValidate(): void
    {
        $black = \Mockery::mock(BlackContract::class);
        $this->container->allows('get')->andReturn(CacheBlack::class)->andReturn($black);
        $black->allows('has')->andReturn(false);
        $black->allows('add')->andReturn(true);
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 'xxx']);
        $token = $this->jwt->generator($tokenObject);
        $parsedToken = $this->jwt->parse($token->toString(), 'default', false);
        $this->assertInstanceOf(Token::class, $parsedToken);
    }

    public function testRefreshToken()
    {
        $black = \Mockery::mock(BlackContract::class);
        $this->container->allows('get')->andReturn(CacheBlack::class)->andReturn($black);
        $black->allows('has')->andReturn(false);
        $black->allows('add')->andReturn(true);
        $tokenObject = new TokenObject();
        $tokenObject->setClaims(['sso_key' => 'foo', 'foo' => 'bar', 'uid' => 'xxx']);
        $token = $this->jwt->generator($tokenObject);
        $refreshedToken = $this->jwt->refreshToken($token->toString());
        $this->assertInstanceOf(Token::class, $refreshedToken);
    }

    public function testLogout()
    {
        $black = \Mockery::mock(CacheBlack::class);
        $this->container->allows('get')->with(CacheBlack::class)->andReturn($black);
        $instance = new TokenObject();
        $instance->setIssuedBy('xxxxx');
        $instance->setClaims([
            'name' => 'zds',
        ]);
        $token = $this->jwt->generator($instance);

        $black->allows('has')->andReturn(false, true);
        $black->allows('add')->andReturn(true);
        $this->assertTrue($this->jwt->logout($token->toString()));
        $this->expectException(TokenValidException::class);
        $this->assertTrue($this->jwt->logout($token->toString()));
    }

    public function testGetTokenDynamicCacheTime(): void
    {
        $black = \Mockery::mock(CacheBlack::class);
        $black->allows('has')->andReturn(false);
        $this->container->allows('get')->with(CacheBlack::class)->andReturn($black);
        $instance = new TokenObject();
        $instance->setIssuedBy('xxxxx');
        $instance->setClaims([
            'name' => 'zds',
        ]);
        $token = $this->jwt->generator($instance);
        $dynamicCacheTime = $this->jwt->getTokenDynamicCacheTime($token->toString());
        $this->assertIsInt($dynamicCacheTime);
        $this->assertEquals(7199, $dynamicCacheTime);
        sleep(1);
        $this->assertEquals(7198, $this->jwt->getTokenDynamicCacheTime($token->toString()));
    }

    public function testGetIndependentTokenVerify(): void
    {
        $config = ['independentTokenVerify' => true];
        $this->assertTrue($this->jwt->getIndependentTokenVerify($config));
    }

    public function testGetValidationData(): void
    {
        $config = $this->jwt->getSceneConfig('default');
        $black = \Mockery::mock(CacheBlack::class);
        $black->allows('has')->andReturn(false);
        $this->container->allows('get')->with(CacheBlack::class)->andReturn($black);
        $instance = new TokenObject();
        $instance->setIssuedBy('xxxxx');
        $instance->setClaims([
            'name' => 'zds',
        ]);
        $token = $this->jwt->generator($instance);
        $signer = $this->jwt->getSigner($config);
        $key = $this->jwt->getKey($config);
        $this->assertTrue($this->jwt->getValidationData($signer, $key, $token->toString()));
    }

    /**
     * @throws JwtConfigException
     */
    public function testGetConfiguration(): void
    {
        $config = $this->jwt->getSceneConfig('default');
        $signer = $this->jwt->getSigner($config);
        $key = $this->jwt->getKey($config);
        $config = $this->jwt->getConfiguration($signer, $key);
        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testGetBlack(): void
    {
        $config = ['black' => CacheBlack::class];
        $this->container->allows('get')
            ->andReturn(CacheBlack::class)
            ->andReturn(\Mockery::mock(CacheBlack::class));
        $black = $this->jwt->getBlack($config);
        $this->assertInstanceOf(BlackContract::class, $black);
    }

    public function testGetBlackWithInvalidClass(): void
    {
        $config = ['black' => 'InvalidClass'];
        $this->expectException(JwtConfigException::class);
        $this->jwt->getBlack($config);
    }

    public function testGetSceneConfig(): void
    {
        $sceneConfig = $this->jwt->getSceneConfig('default');
        $this->assertIsArray($sceneConfig);
    }
}
