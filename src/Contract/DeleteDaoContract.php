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

namespace Mine\Contract;

/**
 * 删除.
 */
interface DeleteDaoContract
{
    /**
     * 删除单条记录 触发 model 事件.
     * @param array|\Closure|int|string $idOrWhere 主键或自定义条件
     * @param bool $force 如果模型有软删除的话是否强制删除
     */
    public function remove(mixed $idOrWhere, bool $force = false): bool;

    /**
     * 删除单条记录 不会触发 model 事件.
     * @param array|\Closure|int|string $id 主键或自定义条件
     */
    public function delete(mixed $id): bool;

    /**
     * 根据主键批量删除.
     */
    public function removeByIds(array $ids): bool;
}
