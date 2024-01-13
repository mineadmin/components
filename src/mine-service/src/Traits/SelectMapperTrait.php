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

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\Mapper;
use Mine\Contract\PageMapperContract;
use Mine\ServiceException;

/**
 * @mixin Mapper
 * @mixin PageMapperContract
 */
trait SelectMapperTrait
{
    public function page(mixed $params = null, int $page = 1, int $size = 10): LengthAwarePaginatorInterface
    {
        return $this->handleSearch(
            $this->handleSelect($this->preQuery()),
            $params
        )->paginate(perPage: $size, page: $page);
    }

    public function count(mixed $params = null): int
    {
        return $this->handleSearch(
            $this->preQuery(),
            $params
        )->count();
    }

    public function list(mixed $params = null): Collection
    {
        return $this->handleSearch(
            $this->handleSelect($this->preQuery()),
            $params
        )->get();
    }

    public function getById(mixed $id): Collection
    {
        return Collection::make($this->getModel()::find($id));
    }

    abstract public function handleSearch(Builder $query, mixed $params = null): Builder;

    protected function handleSelect(Builder $query): Builder
    {
        return $query->select($this->getSelectFields() ?? ['*']);
    }

    /**
     * 查询列.
     * @throws ServiceException
     */
    protected function getSelectFields(): array
    {
        return $this->getModelInstance()->getFillable();
    }

    /**
     * initialization DbBuilder.
     * @throws ServiceException
     */
    protected function preQuery(): Builder
    {
        return $this
            ->getModelQuery();
    }
}
