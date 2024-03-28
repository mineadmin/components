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

use Hyperf\Database\Model\Builder;

interface UserInterface
{
    public function getIdentifier(): string;

    public function getIdentifierName(): string;

    public function getRememberToken(): string;

    public function setRememberToken(string $token): void;

    public function getRememberTokenName(): string;

    public function getPassword(): string;

    public function setPassword(string $password): void;

    public function getSecurityBuilder(): Builder;

    public function setAttribute(string $key, mixed $value);

    public function getAttributes(): array;
}
