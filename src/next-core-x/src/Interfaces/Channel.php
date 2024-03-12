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
    /**
     * Push a message to the specified queue.
     */
    public function push(string $queue, mixed $data): void;

    /**
     * Pulls a message to the specified queue.
     */
    public function pull(string $queue): mixed;

    /**
     * Post a message to the specified queue.
     */
    public function publish(string $queue, mixed $data): void;

    /**
     * Listens for messages from the specified queue and blocks the current thread.
     */
    public function subscribe(string $queue, callable $callback): void;
}
