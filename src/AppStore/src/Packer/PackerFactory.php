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

final class PackerFactory
{
    public function get(string $type = 'json'): PackerInterface
    {
        switch ($type) {
            case 'json':
                return new JsonPacker();
            default:
                throw new \RuntimeException(sprintf('%s Packer type not found', $type));
        }
    }
}
