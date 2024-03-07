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

namespace Mine\NextCoreX\Interfaces;

interface Channel
{
    public function push(string $queue, mixed $data): void;

    public function pull(string $queue): mixed;

    public function publish(string $queue, mixed $data): void;

    public function subscribe(string $queue, callable $callback): void;
}
