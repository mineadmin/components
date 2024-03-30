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

use Mine\CrudBundle\Traits\DeleteDaoTrait;
use Mine\CrudBundle\Traits\SaveOrUpdateDaoTrait;
use Mine\CrudBundle\Traits\SelectDaoTrait;
use Mine\CrudBundle\Traits\UpdateDaoTrait;

abstract class CrudDao extends AbstractDao
{
    use DeleteDaoTrait;
    use SaveOrUpdateDaoTrait;
    use SelectDaoTrait;
    use UpdateDaoTrait;
}
