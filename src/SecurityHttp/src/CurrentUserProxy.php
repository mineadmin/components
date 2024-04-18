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

use Hyperf\Context\Traits\CoroutineProxy;
use Hyperf\Database\Model\Builder;
use Mine\SecurityBundle\Contract\UserInterface;
use Mine\SecurityBundle\Security;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\call;

class CurrentUserProxy implements UserInterface
{
    use CoroutineProxy;

    protected string $proxyKey = 'secret.http.proxy';

    private Security $security;

    public function __construct(
        private string $scene,
        private ContainerInterface $container
    ) {
        $this->security = $this->container->get(Security::class);
    }

    public function __get($name)
    {
        return $this->getAttributes()[$name] ?? null;
    }

    public function getEntity(): UserInterface
    {
        return $this->getSecurity()->getToken()->user($this->scene);
    }

    public function getIdentifier(): string
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function getIdentifierName(): string
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function getRememberToken(): string
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function setRememberToken(string $token): void
    {
        call([$this->getEntity(), __FUNCTION__], func_get_args());
    }

    public function getRememberTokenName(): string
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function getPassword(): string
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function setPassword(string $password): void
    {
        call([$this->getEntity(), __FUNCTION__]);
    }

    public function getSecurityBuilder(): Builder
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function setAttribute(string $key, mixed $value)
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    public function getAttributes(): array
    {
        return call([$this->getEntity(), __FUNCTION__]);
    }

    protected function getSecurity(): Security
    {
        return $this->security;
    }
}
