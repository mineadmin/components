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

namespace Mine\HttpServer;

use Hyperf\Contract\ConfigInterface;

final class Config
{
    public const PREFIX = 'mineadmin.http.server';

    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get(self::PREFIX . '.' . $key, $default);
    }
}
