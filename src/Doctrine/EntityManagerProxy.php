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

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class EntityManagerProxy extends EntityManagerDecorator implements EntityManagerInterface
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
        $manager = $this->registry->getManager();
        if ($manager instanceof EntityManagerInterface) {
            parent::__construct($manager);
        } else {
            throw new \Exception('No EntityManager found');
        }
    }
}
