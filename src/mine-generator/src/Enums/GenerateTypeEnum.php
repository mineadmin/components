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

namespace Mine\Generator\Enums;

/**
 * 执行输出生成类型.
 */
enum GenerateTypeEnum: int
{
    /**
     * 压缩包.
     */
    case ZIP = 1;

    /**
     * 生成到模块.
     */
    case OUTPUT_MODULE = 2;
}
