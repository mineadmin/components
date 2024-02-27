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

use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Model;
use Mine\ServiceException;

/**
 * 更新 Service 契约.
 * @template T
 */
interface UpdateDaoContract
{
    /**
     * 使用模型插入单挑记录,
     * 如果传入的数组 有对应的关联管理则会自动调用对应的关联模型进行关联插入.
     * @return T
     * @throws ServiceException
     */
    public function save(array $data, null|array $withs = null): Model;

    /**
     * 批量插入
     * 将传入的二维数组 foreach 后调用 save 方法批量插入数据.
     * @return Collection<int,T>
     * @throws ServiceException
     */
    public function batchSave(array $data): Collection;

    /**
     * 使用 Db::insert 方法拼接sql 单条插入,不支持关联插入.
     * @throws ServiceException
     */
    public function insert(array $data): bool;

    /**
     * 使用 Db::insert 方法拼接sql进行批量插入.
     * 事务性，如果一条插入失败。会回滚事务
     * @throws ServiceException
     */
    public function batchInsert(array $data): bool;
}
