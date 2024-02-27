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
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Mine\Abstracts\BaseDao;
use Mine\Contract\SaveOrUpdateDaoContract;

/**
 * @mixin BaseDao
 * @implements SaveOrUpdateDaoContract
 */
trait SaveOrUpdateDaoTrait
{
    public function saveOrUpdate(array $data, null|array $where = null): Model
    {
        $keyName = $this->getModelInstance()->getKeyName();
        if ($where === null) {
            return $this->getModel()::updateOrCreate(
                Arr::only($data, [$keyName]),
                $data
            );
        }
        return $this->getModelQuery()->updateOrCreate($where, $data);
    }

    public function batchSaveOrUpdate(
        array $data,
        null|array $whereKeys = null,
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
                        $item,
                        Arr::only($item, $whereKeys)
                    );
                }
            }
            return Collection::make($result);
        });
    }
}
