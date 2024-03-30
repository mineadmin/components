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

namespace Mine\CrudBundle\Tests\Stub;

use Hyperf\Database\Model\Builder;
use Mine\CrudBundle\Abstracts\CrudDao;

class UserDao extends CrudDao
{
    protected static string $model = UserModel::class;

    public function handleSearch(Builder $query, mixed $params = null): Builder
    {
        return $query;
    }
}
