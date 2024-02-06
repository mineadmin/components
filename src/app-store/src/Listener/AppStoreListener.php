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

namespace Xmo\AppStore\Listener;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Xmo\AppStore\Abstracts\AbstractExtension;
use Xmo\AppStore\Annotation\MineExtension;

#[Listener]
class AppStoreListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $isBoot = $event instanceof BootApplication;
        $isCoroutine = $event instanceof MainCoroutineServerStart;
        $extensions = AnnotationCollector::getClassesByAnnotation(MineExtension::class);
        foreach ($extensions as $extension) {
            if (! $extension instanceof AbstractExtension) {
                throw new \RuntimeException(sprintf('%s Class does not inherit correctly from the plugin base class', $extension));
            }
            $isBoot && $extension::boot();
            ! $isBoot && $extension::register($isCoroutine);
        }
    }
}
