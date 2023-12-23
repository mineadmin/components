<?php

namespace Mine\Contract;

use Hyperf\Collection\Collection;
use Hyperf\Contract\LengthAwarePaginatorInterface;
/**
 * 查询 BaseService 锲约
 * @template T
 */
interface PageServiceContract
{
    /**
     * 列表查询
     * @param mixed $params 查询条件
     * @param int $page 页码
     * @param int $size 页数
     * @return LengthAwarePaginatorInterface
     */
    public function page(mixed $params = null,int $page = 1,int $size = 10): LengthAwarePaginatorInterface;

    /**
     * 查询总记录数
     * @param mixed|null $params 查询条件
     * @return int
     */
    public function count(mixed $params = null): int;

    /**
     * 查询所有列表
     * @param mixed $params
     * @return Collection<string,T>
     */
    public function list(mixed $params): Collection;

    /**
     * 根据主键查询一条记录
     * @param mixed $id
     * @return Collection<string,T>
     */
    public function getById(mixed $id): Collection;

}