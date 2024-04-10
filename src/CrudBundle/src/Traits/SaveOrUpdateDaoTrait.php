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

/**
 * @mixin AbstractDao
 */
trait SaveOrUpdateDaoTrait
{
    public function saveOrUpdate(array $data, ?array $where = null): Model
    {
        $query = $this->getModelQuery();
        if ($where === null) {
            $keyName = $this->getModel()->getKeyName();
            return $query->updateOrCreate(
                Arr::only($data, [$keyName]),
                Arr::except($data, [$keyName])
            );
        }
        return $query->updateOrCreate($where, $data);
    }

    public function batchSaveOrUpdate(
        array $data,
        ?array $whereKeys = null,
        int $batchSize = 0
    ): Collection {
        return Db::transaction(function () use ($data, $whereKeys) {
            $result = [];
            foreach ($data as $item) {
                if ($whereKeys === null) {
                    $result[] = $this->saveOrUpdate(
                        $item
                    );
                } else {
                    $result[] = $this->saveOrUpdate(
                        Arr::except($item, $whereKeys),
                        Arr::only($item, $whereKeys)
                    );
                }
            }
            return Collection::make($result);
        });
    }

    public function update(array|int|string $id, array $data, bool $isModel = false): bool
    {
        $query = $this->getModelQuery();
        if (is_array($id)) {
            $query->whereIn($query->getModel()->getKeyName(), $id);
        } else {
            $query->where($query->getModel()->getKeyName(), $id);
        }
        if (! $isModel) {
            return (bool) $query->update($data);
        }
        $entityList = $query->get();
        return Db::transaction(function () use ($entityList, $data) {
            foreach ($entityList as $model) {
                $model->fill($data)->save();
            }
            return true;
        });
    }
}
