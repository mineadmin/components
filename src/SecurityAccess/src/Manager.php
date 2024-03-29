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

namespace Mine\Security\Access;

use Casbin\Enforcer;
use Hyperf\Config\Config;
use Mine\Security\Access\Contract\Access;
use Mine\Security\Access\Exception\AccessException;

class Manager implements Access
{
    public function __construct(
        private readonly Config $config
    ) {}

    public function get(?string $name = null): Enforcer
    {
        if ($name === null) {
            $name = $this->getConfig('default');
        }
        return $this->getAdapter($name);
    }

    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get('access.' . $key, $default);
    }

    protected function getAdapter(string $name): Enforcer
    {
        $adapter = $this->getConfig('component.' . $name);
        if (empty($adapter)) {
            throw new AccessException(sprintf('Access adapter [%s] not exists.', $name));
        }
        if (empty($adapter['construct']) || empty($adapter['enforcer'])) {
            throw new AccessException(sprintf('Access adapter [%s] construct or enforcer not exists.', $name));
        }
        $construct = $adapter['construct'];
        $enforcer = $adapter['enforcer'];
        return new $enforcer(...$construct);
    }
}
