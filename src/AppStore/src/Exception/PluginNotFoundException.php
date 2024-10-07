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

namespace Mine\AppStore\Exception;

class PluginNotFoundException extends \Exception
{
    public function __construct($path)
    {
        parent::__construct(\sprintf('The given directory [%s] is not a valid plugin, probably because it is already installed or the directory is not standardized.', $path));
    }
}
