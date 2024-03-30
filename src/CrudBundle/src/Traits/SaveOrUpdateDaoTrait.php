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
}
