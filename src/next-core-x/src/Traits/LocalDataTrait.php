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
