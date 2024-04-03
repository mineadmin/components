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

class MenuModel extends Model
{
    protected ?string $table = 'menu';

    protected array $fillable = ['id', 'parent_id', 'level', 'name', 'code', 'icon', 'route', 'component', 'redirect', 'is_hidden', 'type', 'status', 'sort', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    protected array $casts = ['id' => 'integer', 'parent_id' => 'integer', 'is_hidden' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 通过中间表获取角色.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'system_role_menu', 'menu_id', 'role_id');
    }
}
