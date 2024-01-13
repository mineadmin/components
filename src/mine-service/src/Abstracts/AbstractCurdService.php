<?php

namespace Mine\Abstracts;

use Hyperf\Context\ApplicationContext;
use Mine\Annotation\CrudModelCollector;
use Mine\Contract\DeleteMapperContract;
use Mine\Contract\PageMapperContract;
use Mine\Contract\SaveOrUpdateMapperContract;
use Mine\Contract\UpdateMapperContract;
use Mine\ServiceException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * crud service
 * @template Mapper
 */
abstract class AbstractCurdService
{
    /**
     * @return Mapper|PageMapperContract|DeleteMapperContract|SaveOrUpdateMapperContract|UpdateMapperContract
     * @throws ServiceException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMapper()
    : UpdateMapperContract|PageMapperContract|SaveOrUpdateMapperContract|DeleteMapperContract
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