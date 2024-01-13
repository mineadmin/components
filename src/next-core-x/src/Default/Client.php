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

namespace Mine\NextCoreX\Default;

use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Mine\NextCoreX\Contracts\ClientContract;

class Client implements ClientContract
{
    public function __construct(
        private SnowflakeIdGenerator $generator
    ) {}

    public function generatorId(): string
    {
        return $this->generator->generate();
    }
}
