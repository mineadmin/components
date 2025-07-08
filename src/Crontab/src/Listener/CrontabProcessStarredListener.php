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

namespace Mine\Crontab\Listener;

use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\ProcessManager;
use Mine\Crontab\CrontabContainer;
use Mine\Crontab\Schedule;

class CrontabProcessStarredListener implements ListenerInterface
{
    public static int $sleep = 30;

    public function __construct(
        private readonly CrontabManager $crontabManager,
        private readonly Schedule $schedule
    ) {}

    public function listen(): array
    {
        return [
            CrontabDispatcherStarted::class,
        ];
    }

    public function process(object $event): void
    {
        Coroutine::create(function () {
            while (ProcessManager::isRunning()) {
                $this->registerCrontab();
                sleep(self::$sleep);
            }
        });
    }

    public function registerCrontab(): void
    {
        $crontabList = $this->schedule->getCrontab();
        foreach ($crontabList as $crontab) {
            if (CrontabContainer::has($crontab->getName())) {
                continue;
            }
            $this->crontabManager->register($crontab);
            CrontabContainer::set($crontab->getName(), 1);
        }
    }
}
