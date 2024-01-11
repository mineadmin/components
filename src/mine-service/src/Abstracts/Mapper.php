<?php

namespace Mine\Abstracts;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;
use Mine\Annotation\CrudModelCollector;
use Mine\ServiceException;

abstract class Mapper
{
    /**
     * 获取模型类名.
     * @return string|Model
     * @throws ServiceException
     */
    public function getModel(): string
    {
        $modelClass = null;
        if (property_exists($this, 'model')) {
            $modelClass = $this->model;
        }
        if (property_exists($this, 'mapper')) {
            $modelClass = $this->mapper;
        }
        if (! empty(CrudModelCollector::list()[static::class])) {
            $modelClass = CrudModelCollector::list()[static::class];
        }
        if (! class_exists($modelClass) || !($modelClass instanceof Model)) {
            throw new ServiceException('The class to which the ' . static::class . ' class belongs was not found');
        }
        return $modelClass;
    }

    /**
     * @throws ServiceException
     */
    public function getModelQuery(): Builder
    {
        return $this->getModel()::query();
    }

    /**
     * @throws ServiceException
     */
    public function getModelInstance(): Builder|\Hyperf\Database\Model\Model
    {
        return $this->getModelQuery()->getModel();
    }
}