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
 * 组件类型.
 */
enum ComponentTypeEnum: string
{
    /**
     * 模态框.
     */
    case MODAL = 'modal';

    /**
     * 拖拽.
     */
    case DRAWER = 'drawer';

    /**
     * tag.
     */
    case TAG = 'tag';
}
