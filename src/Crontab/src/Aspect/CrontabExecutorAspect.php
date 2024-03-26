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

namespace Mine\Crontab\Aspect;

use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mine\Crontab\CrontabContainer;

class CrontabExecutorAspect extends AbstractAspect
{
    public array $classes = [
        Executor::class . '::logResult',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /**
         * @var Crontab $crontab
         * @var bool $isSuccess
         * @var null|\Throwable $throwable
         */
        [$crontab, $isSuccess, $throwable] = $proceedingJoinPoint->getArguments();
        if ($crontab instanceof \Mine\Crontab\Crontab) {
            $callback = $crontab->getCallback();
            if (is_array($callback)) {
                $callback = json_encode($callback, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
            Db::connection(CrontabContainer::$connectionName)
                ->table('crontab_execute_log')
                ->insert([
                    'crontab_id' => $crontab->getCronId(),
                    'name' => $crontab->getName(),
                    'target' => $callback,
                    'status' => $isSuccess ? 1 : 0,
                    'exception_info' => $throwable === null ? '' : $throwable->getMessage(),
                ]);
        }
        return $proceedingJoinPoint->process();
    }
}
