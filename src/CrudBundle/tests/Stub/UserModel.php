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

use Hyperf\Database\Model\Model;

class UserModel extends Model
{
    protected array $fillable = [
        'name',
        'email',
    ];
}
