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

use Mine\Contract\DeleteDaoContract;
use Mine\Contract\PageDaoContract;
use Mine\Contract\SaveOrUpdateDaoContract;
use Mine\Contract\UpdateDaoContract;
use Mine\Traits\DeleteDaoTrait;
use Mine\Traits\SaveOrUpdateDaoTrait;
use Mine\Traits\SelectDaoTrait;
use Mine\Traits\UpdateDaoTrait;

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
