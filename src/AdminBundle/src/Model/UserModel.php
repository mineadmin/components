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

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Mine\SecurityBundle\Contract\UserInterface;

use function Hyperf\Config\config;

class UserModel extends Model implements UserInterface
{
    protected ?string $table = 'users';

    protected array $fillable = ['id', 'username', 'password', 'user_type', 'nickname', 'phone', 'email', 'avatar', 'signed', 'dashboard', 'status', 'login_ip', 'login_time', 'backend_setting', 'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at', 'remark'];

    protected array $casts = ['id' => 'integer', 'status' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 通过中间表关联角色.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'system_user_role', 'user_id', 'role_id');
    }

    /**
     * 通过中间表关联岗位.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(PostModel::class, 'system_user_post', 'user_id', 'post_id');
    }

    /**
     * 通过中间表关联部门.
     */
    public function depts(): BelongsToMany
    {
        return $this->belongsToMany(DepartmentModel::class, 'system_user_dept', 'user_id', 'dept_id');
    }

    public function getIdentifier(): string
    {
        return $this->attributes['username'];
    }

    public function getIdentifierName(): string
    {
        return 'username';
    }

    public function getRememberToken(): string
    {
        throw new \Exception('Method not implemented');
    }

    public function setRememberToken(string $token): void
    {
        throw new \Exception('Method not implemented');
    }

    public function getRememberTokenName(): string
    {
        throw new \Exception('Method not implemented');
    }

    public function getPassword(): string
    {
        return $this->attributes['password'];
    }

    public function setPassword(string $password): void
    {
        $this->attributes['password'] = $password;
    }

    public function getSecurityBuilder(): Builder
    {
        return $this->newQuery();
    }

    public function getConnectionName()
    {
        return config('mineadmin.bundle.database.connection', 'default');
    }
}
