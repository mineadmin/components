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

namespace Mine\Abstracts;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
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
     * @var null|class-string<T>
     */
    public ?string $model = null;

    /**
     * @throws ServiceException
     */
    protected function __handleSelect(Builder $query): Builder
    {
        return $query->select($this->getSelectFields() ?? ['*']);
    }

    /**
     * @throws ServiceException
     */
    public function page(mixed $params = null, int $page = 1, int $size = 10): LengthAwarePaginatorInterface
    {
        return $this->handleSearch(
            $params,
            $this->__handleSelect($this->preQuery())
        )->paginate(perPage: $size, page: $page);
    }

    /**
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
    abstract protected function handleSearch(array $params, Builder $query): Builder;

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
