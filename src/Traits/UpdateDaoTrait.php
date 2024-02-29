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
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\DbConnection\Db;
use Mine\Contract\UpdateDaoContract;
use Mine\ServiceException;

/**
 * @implements UpdateDaoContract<Model>
 */
trait UpdateDaoTrait
{
    public function save(array $data, ?array $withs = null): Model
    {
        return Db::transaction(function () use ($data, $withs) {
            $modelClass = $this->getModel();
            $withAttr = [];
            if ($withs !== null) {
                foreach ($withs as $with) {
                    if (! empty($data[$with])) {
                        $withAttr[$with] = $data[$with];
                        unset($data[$with]);
                    }
                }
            }
            $model = $modelClass::create($data);
            if (! empty($withAttr)) {
                foreach ($withAttr as $with => $attr) {
                    if (method_exists($model, $with)) {
                        /**
                         * @var HasMany|HasOne $withFunc
                         */
                        $withFunc = $model->{$with}();
                        // 如果是二维
                        if (Arr::isAssoc($attr)) {
                            $withFunc->saveMany($attr);
                        } else {
                            $withFunc->save($attr);
                        }
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
                $with = $attr['__with__'] ?? null;
                unset($attr['__with__']);
                $result = $this->save($data, $with);
            }
            return $result;
        });
    }

    public function insert(array $data): bool
    {
        return $this->getModel()::insert($data);
    }

    public function batchInsert(array $data): bool
    {
        Db::transaction(function () use ($data) {
            foreach ($data as $attr) {
                if (! $this->insert($data)) {
                    throw new ServiceException('batch insert fail');
                }
            }
        });
        return true;
    }
}
