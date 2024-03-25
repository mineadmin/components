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

/**
 * @see BootApplicationListener
 */
class Request extends \Hyperf\HttpMessage\Server\Request
{
    public function ip(): string
    {
        return 'xxx';
    }

    public function getAction(): ?string
    {
        return '';
    }
}
