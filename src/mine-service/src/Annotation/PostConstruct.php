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

use Hyperf\Di\Annotation\AbstractAnnotation;

#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
class PostConstruct extends AbstractAnnotation
{
    public function __construct(public int $order = 0) {}

    public function collectMethod(string $className, ?string $target): void
    {
        ComponentCollector::collectPostConstruct($className, $this->order, [
            $className, $target,
        ]);
    }
}
