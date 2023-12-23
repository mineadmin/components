<?php

namespace Mine\Annotation;

use Attribute;

/**
 * Service注解
 * <h1>DependProxy注解的别名</h1>
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Service extends DependProxy
{

}