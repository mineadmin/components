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

namespace Mine\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Mine\ServiceException;

#[Attribute(Attribute::TARGET_FUNCTION)]
class Override extends AbstractAnnotation
{
    public function collectMethod(string $className, ?string $target): void
    {
        $methodReflect = new \ReflectionMethod($className, $target);
        if (
            ! $methodReflect->isStatic()
            || ! $methodReflect->hasReturnType()
            || ! in_array((string) $methodReflect->getReturnType(), ['self', $className])
        ) {
            throw new ServiceException(
                $className . ' The override annotation is used on static methods and returns an instance of the current class'
            );
        }

        ComponentCollector::collectOverride($className, [$className, $target]);
    }
}
