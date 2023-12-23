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

namespace Mine\Listener;

use Hyperf\Context\ApplicationContext;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Mine\Annotation\DependProxyCollector;

class DependProxyListener implements ListenerInterface
{
    public function listen(): array
    {
        return [BootApplication::class];
    }

    public function process(object $event): void
    {
        foreach (DependProxyCollector::list() as $collector) {
            $targets = $collector->values;
            $definition = $collector->provider;
            foreach ($targets as $target) {
                ApplicationContext::getContainer()
                    ->define($definition, $target);
            }
        }
    }
}
