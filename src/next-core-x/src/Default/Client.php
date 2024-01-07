<?php

namespace Mine\NextCoreX\Default;

use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Mine\NextCoreX\Contracts\ClientContract;

class Client implements ClientContract
{
    public function __construct(
        private SnowflakeIdGenerator $generator
    ){}

    /**
     * @inheritDoc
     */
    public function generatorId(): string
    {
        return $this->generator->generate();
    }
}