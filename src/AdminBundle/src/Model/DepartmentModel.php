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

namespace Mine\Admin\Bundle\Model;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsToMany;

use function Hyperf\Config\config;

class DepartmentModel extends Model
{
    protected ?string $table = 'department';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'parent_id', 'level', 'name', 'leader', 'phone', 'status', 'sort', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'parent_id' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 通过中间表获取角色.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'system_role_dept', 'dept_id', 'role_id');
    }

    /**
     * 通过中间表关联部门.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'system_user_dept', 'dept_id', 'user_id');
    }

    /**
     * 通过中间表关联部门.
     */
    public function leader(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'system_dept_leader', 'dept_id', 'user_id');
    }

    public function getConnectionName()
    {
        return config('mineadmin.bundle.database.connection', 'default');
    }
}
