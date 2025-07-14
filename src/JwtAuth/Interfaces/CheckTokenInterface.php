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

namespace Mine\JwtAuth\Interfaces;

use Lcobucci\JWT\UnencryptedToken;

/**
 * @deprecated v3.1 Changed to trigger via token parsing event
 */
interface CheckTokenInterface
{
    public function checkJwt(UnencryptedToken $token): void;
}
