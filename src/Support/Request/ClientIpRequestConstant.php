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

namespace Mine\Support\Request;

final class ClientIpRequestConstant
{
    public const HEADER_FORWARDED = 0b000001; // When using RFC 7239

    public const HEADER_X_FORWARDED_FOR = 0b000010;

    public const HEADER_X_FORWARDED_HOST = 0b000100;

    public const HEADER_X_FORWARDED_PROTO = 0b001000;

    public const HEADER_X_FORWARDED_PORT = 0b010000;

    public const HEADER_X_FORWARDED_PREFIX = 0b100000;

    public const FORWARDED_PARAMS = [
        self::HEADER_X_FORWARDED_FOR => 'for',
        self::HEADER_X_FORWARDED_HOST => 'host',
        self::HEADER_X_FORWARDED_PROTO => 'proto',
        self::HEADER_X_FORWARDED_PORT => 'host',
    ];

    public const TRUSTED_HEADERS = [
        self::HEADER_FORWARDED => 'forwarded',
        self::HEADER_X_FORWARDED_FOR => 'x-forwarded-for',
        self::HEADER_X_FORWARDED_HOST => 'x-forwarded-host',
        self::HEADER_X_FORWARDED_PROTO => 'x-forwarded-proto',
        self::HEADER_X_FORWARDED_PORT => 'x-forwarded-port',
        self::HEADER_X_FORWARDED_PREFIX => 'x-forwarded-prefix',
    ];
}
