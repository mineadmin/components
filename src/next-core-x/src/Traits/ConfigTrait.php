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
use Hyperf\Contract\ConfigInterface;

trait ConfigTrait
{
    public function getConfig(string $key, mixed $default = null)
    {
        return ApplicationContext::getContainer()
            ->get(ConfigInterface::class)
            ->get('next-core-x.' . $key, $default);
    }
}
