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

namespace Mine\NextCoreX\Channel;

use Hyperf\Redis\Redis;
use Mine\NextCoreX\ReadConfig;

class RedisChannel extends AbstractChannel
{
    protected string $configPrefix = 'redis';

    public function __construct(
        ReadConfig $config,
        private readonly Redis $redis,
    ) {
        parent::__construct($config);
    }

    public function push(string $queue, mixed $data): void
    {
        $this->redis->rPush(
            $this->scan($queue),
            $this->getSerialize()->encode($data)
        );
    }

    public function pull(string $queue): mixed
    {
        return $this->getSerialize()->decode($this->redis->lPop($this->scan($queue)));
    }

    public function publish(string $queue, mixed $data): void
    {
        $this->redis->publish($this->scan($queue), $this->getSerialize()->encode($data));
    }

    public function message($redis, string $channel, mixed $data, string $queue, callable $callback): void
    {
        if (empty($data)) {
            return;
        }
        $data = $this->getSerialize()->decode($data);
        $callback($data, $queue, $callback, $redis, $channel);
    }

    public function subscribe(string $queue, callable $callback): void
    {
        $this->redis->subscribe([$this->scan($queue)], [$this, 'message']);
    }

    protected function scan(string $queue): string
    {
        return sprintf($this->getChannelPrefix(), $queue);
    }

    private function getChannelPrefix(): string
    {
        return $this->getConfig('channel');
    }
}
