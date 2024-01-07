<?php

namespace Mine\NextCoreX\Traits;

use Hyperf\Context\ApplicationContext;
use Mine\NextCoreX\Contracts\ClientContract;

trait ClientTrait
{
    use ConfigTrait;

    protected function getClientContract(): ClientContract
    {
        return ApplicationContext::getContainer()->get(
            $this->getConfig('contracts.clientContract')
        );
    }
}