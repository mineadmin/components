<?php

namespace Mine\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class CrudModel extends AbstractAnnotation
{
    public function __construct(
        public string $model
    ){}

    public function collectClass(string $className): void
    {
        CrudModelCollector::collect($className,$this->model);
    }
}