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
 * 更新 Service 契约.
 * @template T
 */
interface UpdateServiceContract
{
    /**
     * 使用模型插入单挑记录,
     * 如果传入的数组 有对应的关联管理则会自动调用对应的关联模型进行关联插入.
     */
    public function save(array $data, null|array $withs = null): bool;

    /**
     * 批量插入
     * 将传入的二维数组 foreach 后调用 save 方法批量插入数据.
     */
    public function batchSave(array $data): bool;

    /**
     * 使用 Db::insert 方法拼接sql 单条插入,不支持关联插入.
     */
    public function insert(array $data): bool;

    /**
     * 使用 Db::insert 方法拼接sql进行批量插入.
     */
    public function batchInsert(array $data): bool;
}
