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

namespace Xmo\AppStore\Service;

use GuzzleHttp\Exception\GuzzleException;

interface AppStoreService
{
    /**
     * Get all locally installed extensions.
     * @throws \JsonException
     */
    public function getLocalExtensions(): array;

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function request(string $uri, array $data = []): array;

    /**
     * Read the specified directory to get the extension details.
     * @throws \JsonException
     */
    public function readExtensionInfo(string $path): array;

    /**
     * Installation of local plug-ins.
     */
    public function installExtension(string $path): void;

    /**
     * Uninstall locally installed plug-ins.
     */
    public function uninstallExtension(string $path): void;
}
