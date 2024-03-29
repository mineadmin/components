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

namespace Mine\Security\Http;

use Hyperf\Context\Context;
use Lcobucci\JWT\Token;
use Mine\Security\Http\Support\Jwt;
use Mine\SecurityBundle\AbstractUserProvider;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\UserInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class UserProvider extends AbstractUserProvider
{
    public function __construct(
        EventDispatcherInterface $dispatcher,
        Config $config,
        private readonly Jwt $jwt
    ) {
        parent::__construct($dispatcher, $config);
    }

    public static function setScene(string $scene): void
    {
        Context::set(static::class . 'scene', $scene);
    }

    public static function getScene(): string
    {
        return Context::get(static::class . 'scene', 'default');
    }

    public function updateRememberToken(UserInterface $user, string $token): bool
    {
        throw new \Exception('Method not implemented');
    }

    public function retrieveById(mixed $identifier): ?object
    {
        $entity = parent::retrieveById($identifier);
        return $entity ? $this->generatorToken($entity) : null;
    }

    public function retrieveByCredentials(array $credentials): ?object
    {
        $entity = $this->credentials($credentials);
        return $entity ? $this->generatorToken($entity) : null;
    }

    private function generatorToken(UserInterface $user): Token
    {
        $attribute = $user->getAttributes();
        $clams = [];
        foreach ($attribute as $key => $value) {
            if ($value === $user->getPassword()) {
                continue;
            }
            $clams['__attribute__' . $key] = $value;
        }
        $tokenInstance = new TokenObject();
        $tokenInstance->setClaims($clams);
        $tokenInstance->setIssuedBy($user->getIdentifier());
        return $this->jwt->generator($tokenInstance, self::getScene());
    }
}
