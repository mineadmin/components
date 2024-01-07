<?php

namespace Mine\NextCoreX\Default;

use Hyperf\Redis\Redis;
use Mine\NextCoreX\Contracts\LocalStoreContract;

class LocalStore implements LocalStoreContract
{
    private string $prefix = 'next:core:x';

    public function __construct(
        readonly private Redis $redis
    ){}

    public function get(string $key, mixed $default = null): mixed
    {
        return unserialize($this->redis->get($this->prefix.':'.$key)) ?? $default;
    }

    public function set(string $key, mixed $value): bool
    {
        return $this->redis->set($this->prefix.':'.$key,serialize($value));
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefix.':'.$key);
    }
}