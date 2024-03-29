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

namespace Mine\SecurityBundle\Context;

use Hyperf\Context\Context as CRT;
use Mine\SecurityBundle\Contract\ContextInterface;

class Context implements ContextInterface
{
    public const CONTEXT_PREFIX = 'mine.security.context';

    public function get(string $name, mixed $default = null): mixed
    {
        return CRT::get(self::CONTEXT_PREFIX . '.' . $name, $default);
    }

    public function has(string $name): bool
    {
        return CRT::has(self::CONTEXT_PREFIX . '.' . $name);
    }

    public function set(string $name, mixed $value): void
    {
        CRT::set(self::CONTEXT_PREFIX . '.' . $name, $value);
    }

    public function getOrSet(string $name, mixed $callable): mixed
    {
        return CRT::getOrSet(self::CONTEXT_PREFIX . '.' . $name, $callable);
    }
}
