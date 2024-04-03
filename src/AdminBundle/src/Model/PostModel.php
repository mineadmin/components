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

class PostModel extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'system_post';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'code', 'sort', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'sort' => 'integer', 'status' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 通过中间表获取用户.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'system_user_post', 'post_id', 'user_id');
    }
}
