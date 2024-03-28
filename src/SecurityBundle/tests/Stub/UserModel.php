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

namespace Mine\SecurityBundle\Tests\Stub;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\SecurityBundle\Contract\UserInterface;

class UserModel extends Model implements UserInterface
{
    public function getIdentifier(): string
    {
        return 'zds@qq.com';
    }

    public function getIdentifierName(): string
    {
        return 'email';
    }

    public function getRememberToken(): string
    {
        return $this->remember_token;
    }

    public function setRememberToken(string $token): void
    {
        $this->remember_token = $token;
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getPassword(): string
    {
        return $this->attributes['password'] ?? '123456';
    }

    public function setPassword(string $password): void
    {
        $this->attributes['password'] = $password;
    }

    public function getSecurityBuilder(): Builder
    {
        return ApplicationContext::getContainer()->get('mocker.builder');
    }
}
