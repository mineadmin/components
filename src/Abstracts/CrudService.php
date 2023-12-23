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

use Mine\Contract\DeleteServiceContract;
use Mine\Contract\PageServiceContract;
use Mine\Contract\SaveOrUpdateServiceContract;
use Mine\Contract\UpdateServiceContract;
use Mine\Traits\DeleteServiceTrait;
use Mine\Traits\SaveOrUpdateServiceTrait;
use Mine\Traits\UpdateServiceTrait;

/**
 * CrudService.
 */
abstract class CrudService extends AbstractPageService implements PageServiceContract, UpdateServiceContract, SaveOrUpdateServiceContract, DeleteServiceContract
{
    use UpdateServiceTrait;
    use SaveOrUpdateServiceTrait;
    use DeleteServiceTrait;
}
