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

namespace Mine\Security\Http\Constant;

class TokenValidConstant
{
    // token expired
    public const EXPIRE = 41;

    // The token is blacklisted.
    public const IN_BLACKLIST = 42;

    // Failed token data verification
    public const PARSER_DATA_VALID = 43;

    // Can't find the token.
    public const TOKEN_NOT_FOUND = 44;
}
