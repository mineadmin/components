<?php

namespace Mine\JwtAuth\Interfaces;

use Lcobucci\JWT\UnencryptedToken;

interface CheckTokenInterface
{
    public function checkJwt(UnencryptedToken $token): void;
}