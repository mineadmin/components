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

namespace Mine\CrudBundle\Contract;

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
    public function save(array $data, ?array $relations = null): Model;

    /**
     * 批量插入
     * 将传入的二维数组 foreach 后调用 save 方法批量插入数据.
     * @return Collection<int,T>
     * @throws ServiceException
     */
    public function batchSave(array $data): Collection;

    /**
     * 使用 insert 方法插入,不支持关联插入.
     * @throws ServiceException
     */
    public function insert(array $data): bool;
}
