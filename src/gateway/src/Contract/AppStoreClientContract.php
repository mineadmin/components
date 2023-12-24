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

namespace Mine\Gateway\Contract;

use Hyperf\Collection\Collection;
use Psr\Http\Message\ResponseInterface;

interface AppStoreClientContract
{
    /**
     * 获取应用列表.
     */
    public function getApplication(array $params = []): Collection;

    /**
     * 获取扩展列表.
     */
    public function getExtensions(array $params = []): Collection;

    /**
     * 用于发起特定请求的方法.
     */
    public function request(
        string $method,
        string $uri,
        array $data = []
    ): ResponseInterface;
}
