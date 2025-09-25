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

namespace Mine\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as ManagerRegistryInterface;

readonly class Factory
{
    public function __construct(
        private Config $config
    ) {}

    public function makeRegistry(): ManagerRegistryInterface
    {
        $databases = $this->config->get('database');
        $connections = [];

        foreach ($databases as $name => $database) {
            $connections[$name] = $name;
        }

        $default = 'default';
        return new ManagerRegistry('mine', $connections, $connections, $default, $default, EntityManagerInterface::class);
    }
}
