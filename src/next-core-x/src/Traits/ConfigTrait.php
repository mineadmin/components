<?php

namespace Mine\NextCoreX\Traits;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;

trait ConfigTrait
{
    public function getConfig(string $key,mixed $default = null)
    {
        return ApplicationContext::getContainer()
            ->get(ConfigInterface::class)
            ->get('next-core-x.'.$key,$default);
    }
}