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

use Mine\SecurityBundle\Contract\ContextInterface;
use Mine\SecurityBundle\Contract\TokenInterface;
use Mine\SecurityBundle\Contract\UserProviderInterface;
use Psr\Container\ContainerInterface;

final class Security
{
    public function __construct(
        private readonly Config $config,
        private readonly ContainerInterface $container
    ) {}

    public function getToken(): TokenInterface
    {
        return $this->container->get($this->config->get('token'));
    }

    public function getContext(): ContextInterface
    {
        return $this->container->get($this->config->get('context'));
    }

    public function getUserProvider(): UserProviderInterface
    {
        return $this->container->get($this->config->get('user_provider'));
    }
}
