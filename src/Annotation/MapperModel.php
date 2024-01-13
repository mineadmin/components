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

#[\Attribute(\Attribute::TARGET_CLASS)]
class MapperModel extends AbstractAnnotation
{
    public function __construct(
        public string $model
    ) {}

    public function collectClass(string $className): void
    {
        CrudModelCollector::collect($className, $this->model);
    }
}
