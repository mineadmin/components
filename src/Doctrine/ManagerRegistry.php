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
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;

class ManagerRegistry extends AbstractManagerRegistry implements \Doctrine\Persistence\ManagerRegistry
{
    private array $managers = [];

    protected function getService(string $name): object
    {
        $contextKey = sprintf('doctrine.manager.%s', $name);
        
        return Context::getOrSet($contextKey, function () use ($name) {
            $factory = ApplicationContext::getContainer()->get(EntityManagerFactory::class);
            return $factory->create($name);
        });
    }

    protected function resetService(string $name): void
    {
        $contextKey = sprintf('doctrine.manager.%s', $name);

        if (Context::has($contextKey)) {
            $manager = Context::get($contextKey);
            if (method_exists($manager, 'close')) {
                $manager->close();
            }
            Context::destroy($contextKey);
        }
        
        // 同时清理传统缓存（向后兼容）
        if (isset($this->managers[$name])) {
            unset($this->managers[$name]);
        }
    }
}
