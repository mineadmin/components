<?php

namespace Mine\Contract;

/**
 * 更新|插入 Service
 */
interface SaveOrUpdateServiceContract
{
    /**
     * 单条记录插入或更新,
     * 只传入 data 时,策略为当 model 主键不存在时就插入一条数据
     * 当 model主键存在时则为更新
     * @param array $data
     * 传入 where 策略1 不起效，当传入的 where 条件存在时则更新
     * 不存在时则会将 data,where merge后的数据作为主参数调用Model的create方法插入
     * @param array|null $where
     * @return bool
     */
    public function saveOrUpdate(array $data,array|null $where = null): bool;

    /**
     * 批量插入更新
     * @param array $data
     * @param array|null $whereKeys 对应的key值
     * @param int $batchSize 分批处理数量
     * @return bool
     */
    public function batchSaveOrUpdate(
        array $data,
        array|null $whereKeys = null,
        int $batchSize = 0
    ): bool;
}