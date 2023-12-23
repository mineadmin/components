<?php

namespace Mine\Abstracts;

use Hyperf\Database\Model\Builder;
use Mine\Contract\DeleteServiceContract;
use Mine\Contract\PageServiceContract;
use Mine\Contract\SaveOrUpdateServiceContract;
use Mine\Contract\UpdateServiceContract;
use Mine\Traits\DeleteServiceTrait;
use Mine\Traits\SaveOrUpdateServiceTrait;
use Mine\Traits\UpdateServiceTrait;

/**
 * CrudService
 */
abstract class CrudService extends AbstractPageService
    implements  PageServiceContract,
                UpdateServiceContract,
                SaveOrUpdateServiceContract,
                DeleteServiceContract
{
    use UpdateServiceTrait,
        SaveOrUpdateServiceTrait,
        DeleteServiceTrait;
}