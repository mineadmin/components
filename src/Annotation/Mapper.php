<?php

namespace Mine\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Mapper extends AbstractAnnotation
{
    public function __construct(
        public string $value
    ){}

    public function collectClass(string $className): void
    {
        CrudModelCollector::collectMapper($className,$this->value);
    }
}