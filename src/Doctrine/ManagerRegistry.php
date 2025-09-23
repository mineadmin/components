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

use Doctrine\Persistence\AbstractManagerRegistry;

class ManagerRegistry extends AbstractManagerRegistry implements \Doctrine\Persistence\ManagerRegistry
{
    protected function getService(string $name): object {}

    protected function resetService(string $name): void {}
}
