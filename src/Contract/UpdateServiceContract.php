<?php

namespace Mine\Contract;

/**
 * 更新 Service 契约
 * @template T
 */
interface UpdateServiceContract
{
    /**
     * 使用模型插入单挑记录,
     * 如果传入的数组 有对应的关联管理则会自动调用对应的关联模型进行关联插入
     * @param array $data
     * @param array|null $withs
     * @return bool
     */
    public function save(array $data,array|null $withs = null): bool;

    /**
     * 批量插入
     * 将传入的二维数组 foreach 后调用 save 方法批量插入数据
     * @param array $data
     * @return bool
     */
    public function batchSave(array $data): bool;

    /**
     * 使用 Db::insert 方法拼接sql 单条插入,不支持关联插入
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool;

    /**
     * 使用 Db::insert 方法拼接sql进行批量插入
     * @param array $data
     * @return bool
     */
    public function batchInsert(array $data): bool;
}