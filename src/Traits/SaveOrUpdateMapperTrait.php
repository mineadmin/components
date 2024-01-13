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

namespace Mine\Traits;

use Hyperf\Collection\Arr;
use Hyperf\DbConnection\Annotation\Transactional;
use Mine\ServiceException;

trait SaveOrUpdateMapperTrait
{
    /**
     * 单条记录插入或更新,
     * 只传入 data 时,策略为当 model 主键不存在时就插入一条数据
     * 当 model主键存在时则为更新.
     * @throws ServiceException
     */
    public function saveOrUpdate(array $data, null|array $where = null): bool
    {
        $keyName = $this->getModelInstance()->getKeyName();
        if ($where === null) {
            $this->getModelQuery()->updateOrCreate(
                Arr::only($data, [$keyName]),
                $data
            );
            return true;
        }
        $this->getModelQuery()->updateOrCreate($where, $data);
        return true;
    }

    /**
     * 批量插入更新.
     * @param null|array $whereKeys 对应的key值
     * @param int $batchSize 分批处理数量
     * @throws ServiceException
     */
    #[Transactional]
    public function batchSaveOrUpdate(
        array $data,
        null|array $whereKeys = null,
        int $batchSize = 0
    ): bool {
        foreach ($data as $item) {
            if ($whereKeys === null) {
                $this->saveOrUpdate(
                    $item
                );
            } else {
                $this->saveOrUpdate(
                    $item,
                    Arr::only($item, $whereKeys)
                );
            }
        }
        return true;
    }
}
