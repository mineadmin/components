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

namespace Mine\CrudBundle\Traits;

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Mine\CrudBundle\Abstracts\AbstractDao;
use Mine\CrudBundle\Exception\CrudException;

/**
 * @mixin AbstractDao
 */
trait UpdateDaoTrait
{
    public function save(array $data, ?array $relations = null): Model
    {
        return Db::transaction(function () use ($data, $relations) {
            $modelQuery = $this->getModelQuery();
            $model = $modelQuery->create($data);
            if (! empty($relations)) {
                foreach ($relations as $relationName => $relationData) {
                    $relation = $modelQuery->getRelation($relationName);
                    if (Arr::isAssoc($relationData)) {
                        $relation->save($relationData);
                    } else {
                        $relation->insert($relationData);
                    }
                }
            }
            return $model;
        });
    }

    public function batchSave(array $data): Collection
    {
        return Db::transaction(function () use ($data) {
            $result = [];
            foreach ($data as $attr) {
                $result[] = $this->save($attr);
            }
            return Collection::make($result);
        });
    }

    public function insert(array $data): bool
    {
        return $this->getModelQuery()->insert($data);
    }

    public function batchInsert(array $data): bool
    {
        Db::transaction(function () use ($data) {
            foreach ($data as $datum) {
                if (! $this->insert($datum)) {
                    throw new CrudException('batch insert fail');
                }
            }
        });
        return true;
    }
}
