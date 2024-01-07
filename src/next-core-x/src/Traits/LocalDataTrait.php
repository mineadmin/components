<?php

namespace Mine\NextCoreX\Traits;

use Hyperf\Context\ApplicationContext;
use Mine\NextCoreX\Contracts\LocalStoreContract;

trait LocalDataTrait
{
    use ConfigTrait;

    public function getLocalData(): LocalStoreContract
    {
        return ApplicationContext::getContainer()->get(
            $this->getConfig('contracts.localStoreContract')
        );
    }
}