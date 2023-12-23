<?php

namespace Mine\Traits;

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Annotation\Transactional;
use Mine\ServiceException;

trait SaveOrUpdateServiceTrait
{
    use GetModelTrait;

    /**
     * 单条记录插入或更新,
     * 只传入 data 时,策略为当 model 主键不存在时就插入一条数据
     * 当 model主键存在时则为更新
     * @param array $data
     * 传入 where 策略1 不起效，当传入的 where 条件存在时则更新
     * 不存在时则会将 data,where merge后的数据作为主参数调用Model的create方法插入
     * @param array|null $where
     * @return bool
     * @throws ServiceException
     */
    public function saveOrUpdate(array $data,array|null $where = null): bool
    {
        $keyName = $this->getModelInstance()->getKeyName();
        if ($where === null){
            $this->getModelQuery()->updateOrCreate(
                Arr::only($data,[$keyName]),$data);
            return true;
        }
        $this->getModelQuery()->updateOrCreate($where,$data);
        return true;
    }


    /**
     * 批量插入更新
     * @param array $data
     * @param array|null $whereKeys 对应的key值
     * @param int $batchSize 分批处理数量
     * @return bool
     * @throws ServiceException
     */
    #[Transactional]
    public function batchSaveOrUpdate(
        array $data,
        array|null $whereKeys = null,
        int $batchSize = 0
    ): bool
    {
        foreach ($data as $item){
            if ($whereKeys === null) {
                $this->saveOrUpdate(
                    $item
                );
            }else{
                $this->saveOrUpdate(
                    $item,Arr::only($item,$whereKeys)
                );
            }
        }
        return true;
    }
}