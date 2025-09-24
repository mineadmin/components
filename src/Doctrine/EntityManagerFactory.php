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

use Doctrine\ORM\EntityManager;
use Mine\Doctrine\Pool\PoolFactory;

final readonly class EntityManagerFactory
{
    public function __construct(
        private PoolFactory $poolFactory,
        private ORMSetupFactory $ORMSetupFactory
    ) {}

    public function create(string $poolName = 'default'): EntityManager
    {
        return new EntityManager($this->poolFactory->getPool($poolName)->get()->getConnection(), $this->ORMSetupFactory->make());
    }
}
