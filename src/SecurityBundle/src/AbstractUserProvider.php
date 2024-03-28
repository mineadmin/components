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

namespace Mine\SecurityBundle;

use Hyperf\Database\Model\Builder;
use Mine\SecurityBundle\Contract\UserInterface;
use Mine\SecurityBundle\Contract\UserProviderInterface;
use Mine\SecurityBundle\Event\Login;
use Mine\SecurityBundle\Event\Validated;
use Mine\SecurityBundle\Event\Verified;
use Mine\SecurityBundle\Exception\NotFoundUserEntityException;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\value;

abstract class AbstractUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Config $config
    ) {}

    public function retrieveByToken(string $token): ?object
    {
        return value(function (Builder $builder, UserInterface $user, string $token) {
            return $builder->where($user->getRememberTokenName(), $token)->first();
        }, $this->getUserEntity()->getSecurityBuilder(), $this->getUserEntity(), $token);
    }

    public function updateRememberToken(UserInterface $user, string $token): bool
    {
        return value(function (Builder $builder, UserInterface $user, string $token) {
            return $builder->update([
                $user->getRememberTokenName() => $token,
            ]);
        }, $user->getSecurityBuilder(), $user, $token);
    }

    public function retrieveById(mixed $identifier): ?object
    {
        return value(
            function (Builder $builder, UserInterface $entity, mixed $identifier) {
                return $builder->where($entity->getIdentifierName(), $identifier)->first();
            },
            $this->getUserEntity()->getSecurityBuilder(),
            $this->getUserEntity(),
            $identifier
        );
    }

    public function credentials(array $credentials): false|UserInterface
    {
        $userEntity = $this->getUserEntity();
        $builder = $userEntity->getSecurityBuilder();
        $identifierName = $userEntity->getIdentifierName();
        if (isset($credentials[$identifierName])) {
            /**
             * @var UserInterface $entity
             */
            $entity = $builder->where($identifierName, $credentials[$identifierName])->first();
            if ($entity === null) {
                return false;
            }
            if ($this->verifyPassword($entity, $credentials['password'])) {
                $this->dispatcher->dispatch(new Login($entity));
                return $entity;
            }
        }
        return false;
    }

    protected function verifyPassword(UserInterface $user, string $password): bool
    {
        if (password_verify($password, $user->getPassword())) {
            $this->dispatcher->dispatch(new Verified($user));
            return true;
        }
        $this->dispatcher->dispatch(new Validated($user));
        return false;
    }

    protected function getUserEntity(): UserInterface
    {
        $entityClass = $this->config->get('entity', '\\App\\Model\\User');
        if (! class_exists($entityClass)) {
            new NotFoundUserEntityException();
        }
        return new $entityClass();
    }
}
