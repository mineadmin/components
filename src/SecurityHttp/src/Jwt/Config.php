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

namespace Mine\Security\Http\Jwt;

use Hyperf\Contract\ConfigInterface;

class Config
{
    public const PREFIX = 'security.jwt';

    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get(sprintf('%s.%s', self::PREFIX, $key), $default);
    }
}
