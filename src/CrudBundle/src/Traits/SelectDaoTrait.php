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

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\CrudBundle\Abstracts\AbstractDao;
use Mine\CrudBundle\Contract\PageDaoContract;
use Mine\ServiceException;

/**
 * @mixin AbstractDao
 * @mixin PageDaoContract
 */
trait SelectDaoTrait
{
    public function page(mixed $params = null, int $page = 1, int $size = 10): LengthAwarePaginatorInterface
    {
        return $this->handleSearch(
            $this->handleSelect($this->preQuery($this->getRecycle($params))),
            $params
        )->paginate(perPage: $size, page: $page);
    }

    public function total(mixed $params = null): int
    {
        return $this->handleSearch(
            $this->preQuery($this->getRecycle($params)),
            $params
        )->count();
    }

    public function list(mixed $params = null): Collection
    {
        return $this->handleSearch(
            $this->preQuery($this->getRecycle($params)),
            $params
        )->get();
    }

    public function findById(mixed $id): ?Model
    {
        return $this->getModelQuery()->take(1)->find($id);
    }

    abstract public function handleSearch(Builder $query, mixed $params = null): Builder;

    protected function handleSelect(Builder $query): Builder
    {
        return $query->select();
    }

    /**
     * 查询列.
     * @throws ServiceException
     */
    protected function getSelectFields(): array
    {
        return $this->getModel()->getFillable();
    }

    /**
     * initialization DbBuilder.
     * @throws ServiceException
     */
    protected function preQuery(bool $recycle = false): Builder
    {
        return $recycle ? $this->getModel()::onlyTrashed() : $this
            ->getModelQuery();
    }

    private function getRecycle(mixed $params): bool
    {
        if (is_object($params)) {
            $result = get_object_vars($params)['recycle'] ?? false;
        } elseif (is_array($params)) {
            $result = $params['recycle'] ?? false;
        } else {
            $result = false;
        }
        return (bool) $result;
    }
}
