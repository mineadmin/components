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

namespace Xmo\AppStore\Command;

use Hyperf\Command\Command;

abstract class AbstractCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct('mine-extension:' . $this->commandName());
    }

    abstract public function __invoke();

    abstract public function commandName(): string;
}
