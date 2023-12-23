<?php

namespace Mine\Annotation;

use Attribute;

/**
 * Defined注解
 * <h1>DependProxy注解的别名</h1>
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Defined extends DependProxy
{
}