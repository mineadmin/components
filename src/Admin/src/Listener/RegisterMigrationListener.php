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

namespace Mine\Admin\Listener;

use Hyperf\Database\Migrations\Migrator;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

#[Listener]
class RegisterMigrationListener implements ListenerInterface
{
    public function __construct(
        private readonly Migrator $migrator
    ) {}

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->migrator->path(dirname(__DIR__, 2) . '/Migration/Databases');
    }
}
