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

namespace Mine\NextCoreX;

use Hyperf\Contract\ConfigInterface;

final class ReadConfig
{
    final public const PREFIX = 'next-core-x';

    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function get(string $key, mixed $value = null): mixed
    {
        return $this->config->get(self::PREFIX . '.' . $key, $value);
    }
}
