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

namespace Mine\Doctrine;

use Hyperf\Contract\ConfigInterface;

final class Config
{
    private string $key = 'doctrine';

    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function get(?string $key = null): mixed
    {
        return $this->config->get($this->key . ($key ? '.' . $key : ''));
    }

    public function has(?string $key = null): bool
    {
        return $this->config->has($this->key . ($key ? '.' . $key : ''));
    }

    public function set(string $key, mixed $value): void
    {
        $this->config->set($this->key . '.' . $key, $value);
    }
}
