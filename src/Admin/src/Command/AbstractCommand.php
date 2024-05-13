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

namespace Mine\Admin\Command;

use Hyperf\Command\Command;
use Psr\Container\ContainerInterface;

abstract class AbstractCommand extends Command
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('mine:' . $this->name());
    }

    /**
     * 命令名称.
     */
    abstract public function name(): string;
}
