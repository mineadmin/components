<?php

namespace Mine\Contract;

use Closure;

/**
 * 删除 Service
 */
interface DeleteServiceContract
{
    /**
     * 删除单条记录 触发 model 事件
     * @param array|integer|string|Closure $idOrWhere 主键或自定义条件
     * @param bool $force 如果模型有软删除的话是否强制删除
     * @return bool
     */
    public function remove(mixed $idOrWhere, bool $force = false): bool;

    /**
     * 删除单条记录 不会触发 model 事件
     * @param array|integer|string|Closure $id 主键或自定义条件
     * @return bool
     */
    public function delete(mixed $id): bool;

    /**
     * 根据主键批量删除
     * @param array $ids
     * @return bool
     */
    public function removeByIds(array $ids): bool;
}