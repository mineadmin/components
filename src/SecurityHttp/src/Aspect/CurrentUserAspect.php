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

namespace Mine\Security\Http\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mine\Security\Http\Attribute\CurrentUser;

class CurrentUserAspect extends AbstractAspect
{
    public array $annotations = [
        CurrentUser::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return $proceedingJoinPoint->process();
    }
}
