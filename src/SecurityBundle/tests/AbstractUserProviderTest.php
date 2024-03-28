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

namespace Mine\SecurityBundle\Tests;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\SecurityBundle\AbstractUserProvider;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\UserInterface;
use Mine\SecurityBundle\Event\Login;
use Mine\SecurityBundle\Event\Validated;
use Mine\SecurityBundle\Event\Verified;
use Mine\SecurityBundle\Tests\Stub\UserModel;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class AbstractUserProviderTest extends TestCase
{
    use RunTestsInCoroutine;

    protected function setUp(): void
    {
        ApplicationContext::getContainer()->set(ConfigInterface::class, new \Hyperf\Config\Config([
            'encryption' => [
                'key' => 'base64:MhEHk72OcV2ttAljUu9Caaam3iP2BnGcwb6GWKkUfV4=',
                'cipher' => 'AES-256-CBC',
            ],
        ]));
    }

    public function testConstruct(): void
    {
        $instance = new class(\Mockery::mock(EventDispatcherInterface::class), \Mockery::mock(Config::class)) extends AbstractUserProvider {
            public function retrieveByCredentials(array $credentials): ?object
            {
                return null;
            }

            public function validateCredentials(UserInterface $user, array $credentials): bool
            {
                return false;
            }
        };
        $this->assertInstanceOf(AbstractUserProvider::class, $instance);
    }

    public function testCredentials()
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\App\Model\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        $verifyModel = new UserModel();
        $verifyModel->setPassword(password_hash('xxxxxx', PASSWORD_DEFAULT));
        $builder->allows('first')->andReturn(null, new UserModel(), $verifyModel);
        $builder->allows('where')
            ->andReturnUsing(function ($column, $value) use ($builder) {
                if ($column === 'email' && $value === 'zds@qq.com') {
                    return $builder;
                }
            });
        ApplicationContext::getContainer()->set('mocker.builder', $builder);
        $instance = new class($event, $config) extends AbstractUserProvider {
            public function retrieveByCredentials(array $credentials): ?object
            {
                return null;
            }

            public function validateCredentials(UserInterface $user, array $credentials): bool
            {
                return false;
            }
        };
        $this->assertFalse($instance->credentials([
            'email' => 'zds@qq.com',
            'password' => '123456',
        ]));
        $event->allows('dispatch')->andReturnUsing(function ($event) {
            $this->assertInstanceOf(Validated::class, $event);
        }, function ($event) {
            if ($event instanceof Verified) {
                $this->assertInstanceOf(Verified::class, $event);
            } else {
                $this->assertInstanceOf(Login::class, $event);
            }
        });

        $this->assertFalse($instance->credentials([
            'email' => 'zds@qq.com',
            'password' => '123456',
        ]));

        $this->assertInstanceOf(UserInterface::class, $instance->credentials([
            'email' => 'zds@qq.com',
            'password' => 'xxxxxx',
        ]));
    }

    public function testRetrieveById(): void
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\App\Model\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        $verifyModel = new UserModel();
        $verifyModel->setPassword(password_hash('xxxxxx', PASSWORD_DEFAULT));
        $builder->allows('first')->andReturn(null, new UserModel(), $verifyModel);
        $builder->allows('where')
            ->andReturnUsing(function ($column, $value) use ($builder) {
                if ($column === 'email') {
                    return $builder;
                }
            });
        ApplicationContext::getContainer()->set('mocker.builder', $builder);
        $instance = new class($event, $config) extends AbstractUserProvider {
            public function retrieveByCredentials(array $credentials): ?object
            {
                return null;
            }

            public function validateCredentials(UserInterface $user, array $credentials): bool
            {
                return false;
            }
        };
        $this->assertNull($instance->retrieveById(1));
        $this->assertInstanceOf(UserInterface::class, $instance->retrieveById(2));
    }

    public function testUpdateRememberToken(): void
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\App\Model\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        $verifyModel = new UserModel();
        $verifyModel->setPassword(password_hash('xxxxxx', PASSWORD_DEFAULT));
        $builder->allows('update')->andReturnUsing(function ($data) {
            if ($data['remember_token'] === '123456') {
                return true;
            }

            return false;
        });
        ApplicationContext::getContainer()->set('mocker.builder', $builder);
        $instance = new class($event, $config) extends AbstractUserProvider {
            public function retrieveByCredentials(array $credentials): ?object
            {
                return null;
            }

            public function validateCredentials(UserInterface $user, array $credentials): bool
            {
                return false;
            }
        };
        $this->assertTrue($instance->updateRememberToken(new UserModel(), '123456'));
        $this->assertFalse($instance->updateRememberToken(new UserModel(), 'xxx'));
    }

    public function testRetrieveByToken(): void
    {
        $event = \Mockery::mock(EventDispatcherInterface::class);
        $config = \Mockery::mock(Config::class);
        $config->allows('get')
            ->with('entity', '\App\Model\User')
            ->andReturn(UserModel::class);
        $builder = \Mockery::mock(Builder::class);
        $verifyModel = new UserModel();
        $verifyModel->setPassword(password_hash('xxxxxx', PASSWORD_DEFAULT));
        $builder->allows('update')->andReturnUsing(function ($data) {
            if ($data['remember_token'] === '123456') {
                return true;
            }

            return false;
        });
        $builder->allows('where')->andReturnUsing(function ($column, $value) use ($builder) {
            $this->assertEquals('remember_token', $column);
            $this->assertEquals('123456', $value);
            return $builder;
        }, function ($column, $value) use ($builder) {
            $this->assertEquals('remember_token', $column);
            $this->assertNotEquals('123456', $value);
            return $builder;
        });
        $builder->allows('first')->andReturn(new UserModel(), null);
        ApplicationContext::getContainer()->set('mocker.builder', $builder);
        $instance = new class($event, $config) extends AbstractUserProvider {
            public function retrieveByCredentials(array $credentials): ?object
            {
                return null;
            }

            public function validateCredentials(UserInterface $user, array $credentials): bool
            {
                return false;
            }
        };
        $this->assertInstanceOf(UserInterface::class, $instance->retrieveByToken('123456'));
        $this->assertNull($instance->retrieveByToken('11111'));
    }
}
