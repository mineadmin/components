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

namespace Mine\Doctrine\Pool;

use Hyperf\Contract\ContainerInterface;
use Mine\Doctrine\Config;

final class PoolFactory
{
    /**
     * @var Pool[]
     */
    private array $pools = [];

    public function __construct(
        private readonly Config $config,
        private readonly ContainerInterface $container
    ) {}

    public function getPool(string $name): Pool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }
        $this->pools[$name] = new Pool($this->container, $this->config, $name);
        return $this->pools[$name];
    }
}
