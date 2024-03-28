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

namespace Mine\SecurityBundle\Contract;

/**
 * laravel/auth UserProvider.
 */
interface UserProviderInterface
{
    public function retrieveById(mixed $identifier): ?object;

    public function retrieveByToken(string $token): ?object;

    public function updateRememberToken(UserInterface $user, string $token): bool;

    public function retrieveByCredentials(array $credentials): ?object;
}
