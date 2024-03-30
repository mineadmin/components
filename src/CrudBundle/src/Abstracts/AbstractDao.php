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

namespace Mine\CrudBundle\Abstracts;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;

class AbstractDao
{
    protected static string $model = Model::class;

    public function getModel(): Model
    {
        return new static::$model();
    }

    public function getModelQuery(): Builder
    {
        return $this->getModel()->newQuery();
    }
}
