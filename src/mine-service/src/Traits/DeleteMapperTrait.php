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

use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;
use Mine\Abstracts\Mapper;
use Mine\Contract\DeleteMapperContract;
use Mine\ServiceException;

/**
 * @mixin Mapper
 * @implements DeleteMapperContract
 */
trait DeleteMapperTrait
{
    /**
     * @inheritDoc
     */
    public function remove(mixed $idOrWhere, bool $force = false): bool
    {
        return Db::transaction(function ()use ($idOrWhere,$force){
            $modelClass = $this->getModel();
            $query = $modelClass::query();
            /**
             * @var null|bool|Model $instance
             */
            $instance = false;
            if (is_array($idOrWhere)) {
                $instance = $query->where($idOrWhere)->first();
            }
            if (is_callable($idOrWhere)) {
                $instance = $query->where($idOrWhere)->first();
            }
            if ($instance === false) {
                $instance = $query->find($idOrWhere);
            }
            if (empty($instance)) {
                return false;
            }
            if ($force) {
                return $instance->forceDelete();
            }
            return false;
        });
    }

    /**
     * @inheritDoc
     */
    public function delete(mixed $id): bool
    {
        $model = $this->getModel();
        $query = $model::query()->getModel();
        $keyName = $query->getModel()->getKeyName();
        /**
         * @var null|Model $instance
         */
        $instance = false;
        if (is_array($id)) {
            $instance = $query->where($id)->first();
        }
        if (is_callable($id)) {
            $instance = $query->where($id)->first();
        }
        if ($instance === null) {
            $instance = $query->find($id);
        }
        if (empty($instance)) {
            return false;
        }
        return $model::query()
            ->where(
                $keyName,
                $instance->getKey()
            )->delete();
    }

    /**
     * @inheritDoc
     */
    public function removeByIds(array $ids): bool
    {
        $query = $this->getModelQuery();
        $keyName = $query->getModel()->getKeyName();
        return $query->whereIn($keyName, $ids)->delete();
    }
}
