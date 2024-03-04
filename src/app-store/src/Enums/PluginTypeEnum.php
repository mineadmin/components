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

namespace Xmo\AppStore\Enums;

/**
 * Plug-in Type.
 */
enum PluginTypeEnum: string
{
    /**
     * Mix and match, front and back end source code included.
     */
    case Mix = 'mix';

    /**
     * 只包含前端源码
     */
    case Frond = 'frond';

    /**
     * Includes only the back-end source code.
     */
    case Backend = 'backend';

    public static function fromValue(string $value): ?self
    {
        return match (strtolower($value)) {
            'mix' => self::Mix,
            'frond' => self::Frond,
            'backend' => self::Backend,
            default => null
        };
    }
}
