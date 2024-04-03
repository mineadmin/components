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

class RoleModel extends Model
{
    protected ?string $table = 'roles';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'code', 'data_scope', 'status', 'sort', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'data_scope' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 通过中间表获取菜单.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(MenuModel::class, 'system_role_menu', 'role_id', 'menu_id');
    }

    /**
     * 通过中间表获取用户.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'system_user_role', 'role_id', 'user_id');
    }

    /**
     * 通过中间表获取部门.
     */
    public function depts(): BelongsToMany
    {
        return $this->belongsToMany(DepartmentModel::class, 'system_role_dept', 'role_id', 'dept_id');
    }
}
