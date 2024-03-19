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

use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Tappable\HigherOrderTapProxy;
use Mine\Abstracts\AbstractMapper;
use Mine\MineCollection;
use Mine\MineModel;
use Mine\MineResponse;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;

use function Hyperf\Collection\collect;

trait ServiceTrait
{
    /**
     * @var AbstractMapper
     */
    public $mapper;

    /**
     * 获取列表数据.
     */
    public function getList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 从回收站过去列表数据.
     */
    public function getListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getList($params, $isScope);
    }

    /**
     * 获取列表数据（带分页）.
     */
    public function getPageList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 从回收站获取列表数据（带分页）.
     */
    public function getPageListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getPageList($params, $isScope);
    }

    /**
     * 获取树列表.
     */
    public function getTreeList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 从回收站获取树列表.
     */
    public function getTreeListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->mapper->getTreeList($params, $isScope);
    }

    /**
     * 新增数据.
     */
    public function save(array $data): mixed
    {
        return $this->mapper->save($data);
    }

    /**
     * 批量新增.
     */
    public function batchSave(array $collects): bool
    {
        return Db::transaction(function () use ($collects) {
            foreach ($collects as $collect) {
                $this->mapper->save($collect);
            }
            return true;
        });
    }

    /**
     * 读取一条数据.
     */
    public function read(mixed $id, array $column = ['*']): ?MineModel
    {
        return $this->mapper->read($id, $column);
    }

    /**
     * Description:获取单个值
     * User:mike.
     * @return null|HigherOrderTapProxy|mixed|void
     */
    public function value(array $condition, string $columns = 'id')
    {
        return $this->mapper->value($condition, $columns);
    }

    /**
     * Description:获取单列值
     * User:mike.
     * @return null|array
     */
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->mapper->pluck($condition, $columns);
    }

    /**
     * 从回收站读取一条数据.
     * @noinspection PhpUnused
     */
    public function readByRecycle(mixed $id): MineModel
    {
        return $this->mapper->readByRecycle($id);
    }

    /**
     * 单个或批量软删除数据.
     */
    public function delete(array $ids): bool
    {
        return ! empty($ids) && $this->mapper->delete($ids);
    }

    /**
     * 更新一条数据.
     */
    public function update(mixed $id, array $data): bool
    {
        return $this->mapper->update($id, $data);
    }

    /**
     * 按条件更新数据.
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        return $this->mapper->updateByCondition($condition, $data);
    }

    /**
     * 单个或批量真实删除数据.
     */
    public function realDelete(array $ids): bool
    {
        return ! empty($ids) && $this->mapper->realDelete($ids);
    }

    /**
     * 单个或批量从回收站恢复数据.
     */
    public function recovery(array $ids): bool
    {
        return ! empty($ids) && $this->mapper->recovery($ids);
    }

    /**
     * 单个或批量禁用数据.
     */
    public function disable(array $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->mapper->disable($ids, $field);
    }

    /**
     * 单个或批量启用数据.
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        return ! empty($ids) && $this->mapper->enable($ids, $field);
    }

    /**
     * 修改数据状态
     */
    public function changeStatus(mixed $id, string $value, string $filed = 'status'): bool
    {
        return $value == MineModel::ENABLE ? $this->mapper->enable([$id], $filed) : $this->mapper->disable([$id], $filed);
    }

    /**
     * 数字更新操作.
     */
    public function numberOperation(mixed $id, string $field, int $value): bool
    {
        return $this->mapper->numberOperation($id, $field, $value);
    }

    /**
     * 导出数据.
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function export(array $params, ?string $dto, ?string $filename = null, ?\Closure $callbackData = null): ResponseInterface
    {
        if (empty($dto)) {
            return container()->get(MineResponse::class)->error('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->mapper->getModel()->getTable();
        }

        return (new MineCollection())->export($dto, $filename, $this->mapper->getList($params), $callbackData);
    }

    /**
     * 数据导入.
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return Db::transaction(function () use ($dto, $closure) {
            return $this->mapper->import($dto, $closure);
        });
    }

    /**
     * 数组数据转分页数据显示.
     */
    public function getArrayToPageList(?array $params = [], string $pageName = 'page'): array
    {
        $collect = $this->handleArraySearch(collect($this->getArrayData($params)), $params);

        $pageSize = MineModel::PAGE_SIZE;
        $page = 1;

        if ($params[$pageName] ?? false) {
            $page = (int) $params[$pageName];
        }

        if ($params['pageSize'] ?? false) {
            $pageSize = (int) $params['pageSize'];
        }

        $data = $collect->forPage($page, $pageSize)->toArray();

        return [
            'items' => $this->getCurrentArrayPageBefore($data, $params),
            'pageInfo' => [
                'total' => $collect->count(),
                'currentPage' => $page,
                'totalPage' => ceil($collect->count() / $pageSize),
            ],
        ];
    }

    /**
     * 远程通用列表查询.
     */
    public function getRemoteList(array $params = []): array
    {
        $remoteOption = $params['remoteOption'] ?? [];
        unset($params['remoteOption']);
        return $this->mapper->getRemoteList(array_merge($params, $remoteOption));
    }

    /**
     * 数组数据搜索器.
     * @return Collection
     */
    protected function handleArraySearch(\Hyperf\Collection\Collection $collect, array $params): \Hyperf\Collection\Collection
    {
        return $collect;
    }

    /**
     * 数组当前页数据返回之前处理器，默认对key重置.
     */
    protected function getCurrentArrayPageBefore(array &$data, array $params = []): array
    {
        sort($data);
        return $data;
    }

    /**
     * 设置需要分页的数组数据.
     */
    protected function getArrayData(array $params = []): array
    {
        return [];
    }
}
