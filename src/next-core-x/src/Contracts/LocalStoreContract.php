<?php

namespace Mine\NextCoreX\Contracts;

interface LocalStoreContract
{
    public function get(string $key,mixed $default = null): mixed;

    public function set(string $key,mixed $value): bool;

    public function delete(string $key): bool;
}