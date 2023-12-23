<?php

namespace Mine\Abstracts;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Paginator\Paginator;
use Mine\Contract\PageServiceContract;
use Mine\ServiceException;
use Mine\Traits\GetModelTrait;

/**
 * @template T
 */
abstract class AbstractPageService implements PageServiceContract
{
    use GetModelTrait;

    /**
     * @var null|class-string<T> $model
     */
    public ?string $model = null;

    /**
     * 查询列
     * @return array
     * @throws ServiceException
     */
    protected function getSelectFields(): array
    {
        return $this->getModelInstance()->getFillable();
    }

    /**
     * 查询处理
     * @param array $params
     * @param Builder $query
     * @return Builder
     */
    abstract protected function handleSearch(array $params,Builder $query): Builder;


    /**
     * initialization DbBuilder
     * @throws ServiceException
     */
    protected function preQuery(): Builder
    {
        return $this
            ->getModelQuery();
    }

    /**
     * @throws ServiceException
     */
    protected function __handleSelect(Builder $query): Builder
    {
        return $query->select($this->getSelectFields() ?? ['*']);
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function page(mixed $params = null, int $page = 1, int $size = 10): LengthAwarePaginatorInterface
    {
        return $this->handleSearch(
            $params,
            $this->__handleSelect($this->preQuery())
        )->paginate(perPage: $size,page: $page);
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function count(mixed $params = null): int
    {
        return $this->handleSearch(
            $params,
            $this->preQuery()
        )->count();
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function list(mixed $params): Collection
    {
        return $this->handleSearch(
            $params,
            $this->__handleSelect($this->preQuery())
        )->get();
    }

    /**
     * @inheritDoc
     * @throws ServiceException
     */
    public function getById(mixed $id): Collection
    {
        return Collection::make($this->getModel()::find($id));
    }
}