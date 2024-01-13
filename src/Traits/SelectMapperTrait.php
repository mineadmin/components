<?php

namespace Mine\Traits;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\Mapper;
use Mine\ServiceException;

/**
 * @mixin Mapper
 */
trait SelectMapperTrait
{
    /**
     * @throws ServiceException
     */
    protected function handleSelect(Builder $query): Builder
    {
        return $query->select($this->getSelectFields() ?? ['*']);
    }

    /**
     * @throws ServiceException
     */
    public function page(array $params = [], int $page = 1, int $size = 10): LengthAwarePaginatorInterface
    {
        return $this->handleSearch(
            $this->handleSelect($this->preQuery()),
            $params
        )->paginate(perPage: $size, page: $page);
    }

    /**
     * @throws ServiceException
     */
    public function count(array $params = []): int
    {
        return $this->handleSearch(
            $this->preQuery(),
            $params
        )->count();
    }

    /**
     * @throws ServiceException
     */
    public function list(array $params = []): Collection
    {
        return $this->handleSearch(
            $this->handleSelect($this->preQuery()),
            $params
        )->get();
    }

    /**
     * @throws ServiceException
     */
    public function getById(mixed $id): Collection
    {
        return Collection::make($this->getModel()::find($id));
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
     * 查询处理.
     */
    abstract protected function handleSearch(Builder $query,array $params = []): Builder;

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