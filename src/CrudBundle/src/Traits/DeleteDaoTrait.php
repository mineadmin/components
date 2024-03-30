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

use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;
use Mine\CrudBundle\Abstracts\AbstractDao;

/**
 * @mixin AbstractDao
 */
trait DeleteDaoTrait
{
    public function remove(mixed $idOrWhere, bool $force = false): bool
    {
        return Db::transaction(function () use ($idOrWhere, $force) {
            $query = $this->getModelQuery();
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
            return $force ? $instance->forceDelete() : $instance->delete();
        });
    }

    public function delete(mixed $id): bool
    {
        $query = $this->getModelQuery();
        $keyName = $query->getModel()->getKeyName();
        if (is_array($id)) {
            return $query->whereIn($keyName, $id)->delete();
        }
        if (is_callable($id)) {
            return $query->where($id)->delete();
        }
        return (bool) $query
            ->where(
                $keyName,
                $id
            )
            ->delete();
    }

    public function removeByIds(array $ids): bool
    {
        $query = $this->getModelQuery();
        $keyName = $query->getModel()->getKeyName();
        return $query->whereIn($keyName, $ids)->delete();
    }
}
