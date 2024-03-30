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

namespace Mine\Abstracts;

use Mine\CrudBundle\Contract\DeleteDaoContract;
use Mine\CrudBundle\Contract\PageDaoContract;
use Mine\CrudBundle\Contract\SaveOrUpdateDaoContract;
use Mine\CrudBundle\Contract\UpdateDaoContract;
use Mine\CrudBundle\Traits\DeleteDaoTrait;
use Mine\CrudBundle\Traits\SaveOrUpdateDaoTrait;
use Mine\CrudBundle\Traits\SelectDaoTrait;
use Mine\CrudBundle\Traits\UpdateDaoTrait;

/**
 * CrudService.
 * @template T
 * @implements PageDaoContract<T>
 * @implements SaveOrUpdateDaoContract<T>
 */
abstract class CrudDao extends BaseDao implements PageDaoContract, UpdateDaoContract, SaveOrUpdateDaoContract, DeleteDaoContract
{
    use UpdateDaoTrait;
    use SaveOrUpdateDaoTrait;
    use DeleteDaoTrait;
    use SelectDaoTrait;
}
