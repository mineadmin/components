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

namespace Mine\CrudBundle\Contract;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;

/**
 * 查询 BaseService 锲约.
 * @template T for model
 * @template Param for dto
 */
interface PageDaoContract
{
    /**
     * 列表查询.
     * @param array|Param $params 查询条件
     * @param int $page 页码
     * @param int $size 页数
     */
    public function page(mixed $params = null, int $page = 1, int $size = 10): LengthAwarePaginatorInterface;

    /**
     * 查询总记录数.
     * @param array|Param $params 查询条件
     */
    public function total(mixed $params = null): int;

    /**
     * 查询所有列表.
     * @return Collection<string,T>
     */
    public function list(mixed $params = null): Collection;

    /**
     * 根据主键查询一条记录.
     */
    public function findById(mixed $id): ?Model;

    /**
     * @param mixed|Param $params
     */
    public function handleSearch(Builder $query, mixed $params = null): Builder;
}
