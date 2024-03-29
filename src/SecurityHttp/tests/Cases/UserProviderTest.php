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

namespace Mine\Security\Http\Tests\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Builder;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Security\Http\Support\Jwt;
use Mine\Security\Http\Tests\Stub\UserModel;
use Mine\Security\Http\TokenObject;
use Mine\Security\Http\UserProvider;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\UserInterface;
use Mine\SecurityBundle\Event\Login;
use Mine\SecurityBundle\Event\Validated;
use Mine\SecurityBundle\Event\Verified;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class UserProviderTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testSetScene()
    {
        UserProvider::setScene('admin');
        $this->assertEquals('admin', UserProvider::getScene());
    }

    public function testRetrieveByCredentials(): void
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\\App\\Model\\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        ApplicationContext::getContainer()->set('mock.builder', $builder);
        $jwt = \Mockery::mock(Jwt::class);
        $userProvider = new UserProvider($event, $config, $jwt);
        $this->assertInstanceOf(UserProvider::class, $userProvider);
        $this->assertNull($userProvider->retrieveByCredentials([
            'username' => 'admin@qq.com',
            'password' => '123456',
        ]));
        $builder->allows('where')->andReturnUsing(function ($column, $value) use ($builder) {
            if ($column === 'email') {
                $this->assertEquals('xxx@qq.com', $value);
            }
            return $builder;
        });
        $user = new UserModel();
        $user->setPassword(password_hash('123456', PASSWORD_DEFAULT));
        $user2 = clone $user;
        $user2->setPassword(password_hash('1234567', PASSWORD_DEFAULT));
        $builder->allows('first')->andReturn($user, $user2);
        $jwt->allows('generator')->andReturnUsing(function (TokenObject $token) {
            $this->assertEquals('xxx@qq.com', $token->getIssuedBy());
            $this->assertEquals([
                '__attribute__id' => 1,
                '__attribute__email' => 'xxx@qq.com',
            ], $token->getClaims());
            return \Mockery::mock(UnencryptedToken::class);
        });
        $event->allows('dispatch')->andReturnUsing(function ($event) {
            $this->assertInstanceOf(Verified::class, $event);
        }, function ($event) {
            $this->assertInstanceOf(Login::class, $event);
        }, function ($event) {
            $this->assertInstanceOf(Validated::class, $event);
        });
        $this->assertInstanceOf(UnencryptedToken::class, $userProvider->retrieveByCredentials([
            'email' => 'xxx@qq.com',
            'password' => '123456',
        ]));
        $this->assertNull($userProvider->retrieveByCredentials([
            'email' => 'xxx@qq.com',
            'password' => '123456',
        ]));
    }

    public function testRetrieveById()
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\\App\\Model\\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        ApplicationContext::getContainer()->set('mock.builder', $builder);
        $jwt = \Mockery::mock(Jwt::class);
        $userProvider = new UserProvider($event, $config, $jwt);
        $this->assertInstanceOf(UserProvider::class, $userProvider);

        $user = new UserModel();
        $user2 = clone $user;
        $builder->allows('first')->andReturn(null, $user, $user2);
        $builder->allows('where')->andReturn($builder);
        $jwt->allows('generator')->andReturnUsing(function (TokenObject $token) {
            $this->assertEquals('xxx@qq.com', $token->getIssuedBy());
            $this->assertEquals([
                '__attribute__id' => 1,
                '__attribute__email' => 'xxx@qq.com',
            ], $token->getClaims());
            return \Mockery::mock(UnencryptedToken::class);
        });
        $this->assertNull($userProvider->retrieveById('123@qq.com'));
        $this->assertInstanceOf(UnencryptedToken::class, $userProvider->retrieveById('123@qq.com'));
    }

    public function testUpdateRememberToken()
    {
        $reflection = new \ReflectionClass(UserProvider::class);
        $method = $reflection->getMethod('updateRememberToken');
        $instance = \Mockery::mock(UserProvider::class);
        try {
            $method->invokeArgs($instance, [\Mockery::mock(UserInterface::class), '133213123']);
        } catch (\Exception $e) {
            $this->assertEquals('Method not implemented', $e->getMessage());
        }
    }
}
