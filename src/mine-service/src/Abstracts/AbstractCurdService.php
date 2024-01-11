<?php

namespace Mine\Abstracts;

use Hyperf\Context\ApplicationContext;
use Mine\Annotation\CrudModelCollector;
use Mine\Contract\DeleteMapperContract;
use Mine\Contract\PageMapperContract;
use Mine\Contract\SaveOrUpdateMapperContract;
use Mine\Contract\UpdateMapperContract;
use Mine\ServiceException;

/**
 * crud service
 */
abstract class AbstractCurdService
{
    public function getMapper()
    : PageMapperContract | DeleteMapperContract | SaveOrUpdateMapperContract | UpdateMapperContract
    {
        $mapper = null;
        if (property_exists($this,'mapper')){
            $mapper = $this->mapper;
        }
        $mapperCollect = CrudModelCollector::mapperList();
        if (!empty($mapperCollect[static::class])){
            $mapper = ApplicationContext::getContainer()->get($mapperCollect[static::class]);
        }
        if (empty($mapper)) {
            throw new ServiceException('the Service NotFound Mapper ' . static::class);
        }
        return $mapper;
    }
}