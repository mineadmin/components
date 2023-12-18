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

namespace Mine\Gateway;

class AbstractService
{
    protected Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * 授权登录.
     */
    public function auth(string $account, string $password): string
    {
        // todo...
        return 'token';
    }
}
