<?php

namespace Mine\Traits;

use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\DbConnection\Annotation\Transactional;
use Mine\ServiceException;

trait UpdateServiceTrait
{
    use GetModelTrait;

    /**
     * 使用模型插入单挑记录,
     * 如果传入的数组 有对应的关联管理则会自动调用对应的关联模型进行关联插入
     * @param array $data
     * @param array|null $withs
     * @return bool
     * @throws ServiceException
     */
    #[Transactional]
    public function save(array $data,array|null $withs = null): bool
    {
        $modelClass = $this->getModel();
        $withAttr = [];
        if ($withs!== null){
            foreach ($withs as $with){
                if (!empty($data[$with])){
                    $withAttr[$with] = $data[$with];
                    unset($data[$with]);
                }
            }
        }
        $model = $modelClass::create($data);
        if (!empty($withAttr)){
            foreach ($withAttr as $with => $attr){
                if (method_exists($model,$with)){
                    /**
                     * @var HasOne|HasMany $withFunc
                     */
                    $withFunc = $model->{$with}();
                    // 如果是二维
                    if (Arr::isAssoc($attr)){
                        $withFunc->saveMany($attr);
                    }else{
                        $withFunc->save($attr);
                    }
                }
            }
        }
        return true;
    }

    /**
     * 批量插入
     * 将传入的二维数组 foreach 后调用 save 方法批量插入数据
     * @param array $data
     * @return bool
     * @throws ServiceException
     */
    #[Transactional]
    public function batchSave(array $data): bool
    {
        foreach ($data as $attr){
            $with = $attr['__with__'] ?? null;
            unset($attr['__with__']);
            $this->save($data,$with);
        }
        return true;
    }

    /**
     * 使用 Db::insert 方法拼接sql 单条插入,不支持关联插入
     * @param array $data
     * @return bool
     * @throws ServiceException
     */
    public function insert(array $data): bool
    {
        return $this->getModel()::insert($data);
    }

    /**
     * 使用 Db::insert 方法拼接sql进行批量插入
     * @param array $data
     * @return bool
     * @throws ServiceException
     */
    #[Transactional]
    public function batchInsert(array $data): bool
    {
        foreach ($data as $attr){
            $this->insert($data);
        }
        return true;
    }

}