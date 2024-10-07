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

namespace Mine\AppStore\Packer;

class JsonPacker implements PackerInterface
{
    /**
     * @throws \JsonException
     */
    public function unpack(string $body): array
    {
        return json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function pack(array $body): string
    {
        return json_encode($body, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE);
    }
}
