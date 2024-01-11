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
 * 更新|插入 Service.
 */
interface SaveOrUpdateMapperContract
{
    /**
     * 单条记录插入或更新,
     * 只传入 data 时,策略为当 model 主键不存在时就插入一条数据
     * 当 model主键存在时则为更新.
     * @param array $data
     *                    传入 where 策略1 不起效，当传入的 where 条件存在时则更新
     *                    不存在时则会将 data,where merge后的数据作为主参数调用Model的create方法插入
     */
    public function saveOrUpdate(array $data, null|array $where = null): bool;

    /**
     * 批量插入更新.
     * @param null|array $whereKeys 对应的key值
     * @param int $batchSize 分批处理数量
     */
    public function batchSaveOrUpdate(
        array $data,
        null|array $whereKeys = null,
        int $batchSize = 0
    ): bool;
}
