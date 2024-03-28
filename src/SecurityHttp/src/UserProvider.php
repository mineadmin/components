<?php

namespace Mine\Security\Http;

use Mine\SecurityBundle\AbstractUserProvider;
use Mine\SecurityBundle\Contract\UserInterface;

class UserProvider extends AbstractUserProvider
{

    public function retrieveByCredentials(array $credentials): ?object
    {

    }

    public function validateCredentials(UserInterface $user, array $credentials): bool
    {

    }
}