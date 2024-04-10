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

namespace Mine\Admin\Bundle\Contract;

interface DeptServiceInterface extends CrudServiceInterface
{
    /**
     * 获取部门领导列表.
     * Get a list of department heads.
     */
    public function getLeaderList(array $params): array;

    /**
     * 新增部门领导.
     * Additional department heads.
     */
    public function addLeader(array $data): bool;

    /**
     * 删除部门领导.
     * Delete department heads.
     */
    public function delLeader(array $data): bool;

    /**
     * 检查子部门是否存在.
     * Check for the existence of subsectors .
     */
    public function existsChildrenById(int $id): bool;
}
