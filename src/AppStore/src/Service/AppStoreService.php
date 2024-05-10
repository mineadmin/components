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

namespace Mine\AppStore\Service;

use GuzzleHttp\Exception\GuzzleException;

interface AppStoreService
{
    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function request(string $uri, array $data = []): array;

    /**
     * Download the specified plug-in to a local directory.
     */
    public function download(string $identifier, string $version): bool;

    /**
     * Get the details of the specified plugin.
     */
    public function view(string $identifier): array;

    /**
     * Get the list of remote plugins.
     */
    public function list(array $params): array;
}
