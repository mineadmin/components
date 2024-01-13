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

use Mine\Contract\DeleteMapperContract;
use Mine\Contract\PageMapperContract;
use Mine\Contract\SaveOrUpdateMapperContract;
use Mine\Contract\UpdateMapperContract;
use Mine\Traits\DeleteMapperTrait;
use Mine\Traits\SaveOrUpdateMapperTrait;
use Mine\Traits\SelectMapperTrait;
use Mine\Traits\UpdateMapperTrait;

/**
 * CrudService.
 */
abstract class CrudMapper extends Mapper implements PageMapperContract, UpdateMapperContract, SaveOrUpdateMapperContract, DeleteMapperContract
{
    use UpdateMapperTrait;
    use SaveOrUpdateMapperTrait;
    use DeleteMapperTrait;
    use SelectMapperTrait;
}
