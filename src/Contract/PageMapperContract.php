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

namespace Mine\Contract;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;

/**
 * 查询 BaseService 锲约.
 * @template T
 */
interface PageMapperContract
{
    /**
     * 列表查询.
     * @param array $params 查询条件
     * @param int $page 页码
     * @param int $size 页数
     */
    public function page(array $params = [], int $page = 1, int $size = 10): LengthAwarePaginatorInterface;

    /**
     * 查询总记录数.
     * @param array $params 查询条件
     */
    public function count(array $params = []): int;

    /**
     * 查询所有列表.
     * @return Collection<string,T>
     */
    public function list(array $params = []): Collection;

    /**
     * 根据主键查询一条记录.
     * @return Collection<string,T>
     */
    public function getById(mixed $id): Collection;
}
