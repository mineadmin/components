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

namespace Mines\HttpServer\IdeHelpers;

use Mine\HttpServer\Listener\BootApplicationListener;

class Request extends \Hyperf\HttpMessage\Server\Request
{
    /**
     * @see BootApplicationListener
     */
    public function ip(): string
    {
        return 'xxx';
    }
}
