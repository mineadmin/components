<?php

namespace Mine\Admin\Bundle\Command;

use Hyperf\Command\Command;

abstract class AbstractCommand extends Command
{
    abstract function name(): string;

    public function __construct()
    {
        parent::__construct('mine:'.$this->name());
    }
}