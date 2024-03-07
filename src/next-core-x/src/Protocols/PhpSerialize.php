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

namespace Mine\NextCoreX\Protocols;

use Mine\NextCoreX\Interfaces\Serialize;

class PhpSerialize implements Serialize
{
    public function decode(string $data): mixed
    {
        return unserialize($data);
    }

    public function encode(mixed $data): string
    {
        return serialize($data);
    }
}
