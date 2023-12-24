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

namespace Mine\Gateway\Client;

use Hyperf\Collection\Collection;
use Mine\Gateway\Contract\AppStoreClientContract;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements AppStoreClientContract
{
    public function getApplication(array $params = []): Collection {}

    public function getExtensions(array $params = []): Collection
    {
        // TODO: Implement getExtensions() method.
    }

    public function request(string $method, string $uri, array $data = []): ResponseInterface
    {
        // TODO: Implement request() method.
    }
}
