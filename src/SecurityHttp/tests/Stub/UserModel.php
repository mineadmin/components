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

namespace Mine\Security\Http\Tests\Stub;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\SecurityBundle\Contract\UserInterface;

class UserModel extends Model implements UserInterface
{
    protected array $attributes = [
        'id' => 1,
        'email' => 'xxx@qq.com',
        'password' => '',
    ];

    public function getIdentifier(): string
    {
        return $this->attributes['email'];
    }

    public function getIdentifierName(): string
    {
        return 'email';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken(string $token): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getPassword(): string
    {
        return $this->attributes['password'];
    }

    public function setPassword(string $password): void
    {
        $this->attributes['password'] = $password;
    }

    public function getSecurityBuilder(): Builder
    {
        return ApplicationContext::getContainer()->get('mock.builder');
    }
}
