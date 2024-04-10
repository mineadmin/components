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

namespace Mine\Admin\Bundle\Contract;

interface CrudServiceInterface
{
    /**
     * List.
     * 列表.
     */
    public function lst(array $params): array;

    /**
     * 保存/新增 数据.
     * Save/add data.
     */
    public function save(array $params): array;

    /**
     * 根据主键更新指定的数据.
     * Updates the specified data based on the primary key.
     */
    public function update(mixed $id, array $data): bool;

    /**
     * 删除指定的主键列表
     * Delete a specified list of primary keys.
     */
    public function delete(array|int|string $ids): bool;

    /**
     * 真实删除.
     * True Delete.
     */
    public function realDelete(array|int|string $ids): bool;

    /**
     * 回收站列表.
     * Recycle Bin List.
     */
    public function recycle(array $params): array;

    /**
     * 单个或批量从回收站恢复数据.v
     * Recover data from Recycle Bin individually or in batches.v.
     */
    public function recovery(array|int|string $ids): array;

    /**
     * 树列表.
     * Tree list.
     */
    public function tree(): array;

    /**
     * 修改数据状态
     */
    public function changeStatus(mixed $id, string $value, string $filed = 'status'): bool;

    /**
     * 数字更新操作.
     */
    public function numberOperation(mixed $id, string $field, int $value): bool;
}
