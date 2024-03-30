<?php

namespace Mine\Admin\Bundle\Contract;

use Mine\SecurityBundle\Contract\UserInterface;
use Mine\Admin\Bundle\Dto\UserLoginDto;

interface PassportServiceInterface
{
    public function login(UserLoginDto $userLoginDto):null|UserInterface;

    public function logout(null|string $identifier = null): void;
}