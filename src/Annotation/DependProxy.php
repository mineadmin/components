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

use function Hyperf\Support\make;

/**
 * 依赖代理注解，用于平替某个类.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DependProxy extends AbstractAnnotation
{
    public function __construct(public array $values = [], public ?string $provider = null) {}

    public function collectClass(string $className): void
    {
        if (! $this->provider) {
            $this->provider = $className;
        }
        if (count($this->values) == 0 && class_exists($className)) {
            $reflection = new \ReflectionClass($className);
            $interfaces = $reflection->getInterfaces();
            // 按照定义顺序排序接口列表
            uasort($interfaces, function ($a, $b) {
                if (in_array($a->getName(), class_implements($b->getName()))) {
                    return 1;
                }
                if (in_array($b->getName(), class_implements($a->getName()))) {
                    return -1;
                }
                return 0;
            });
            $this->values = array_values($interfaces)[0]->getName();
        }
        DependProxyCollector::setAround($className, $this);
    }
}
