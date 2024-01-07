<?php

namespace Mine\NextCoreX\Contracts;

interface ClientContract
{
    /**
     * 生产全局不唯一的 fd
     * @return string
     */
    public function generatorId(): string;
}