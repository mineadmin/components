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

namespace Mine\AppStore\Command;

use Hyperf\Command\Command;

abstract class AbstractCommand extends Command
{
    protected const COMMAND_NAME = null;

    public function __construct(?string $name = null)
    {
        parent::__construct('mine-extension:' . static::commandName());
    }

    abstract public function __invoke(): int;

    public static function commandName(): string
    {
        if (static::COMMAND_NAME === null) {
            throw new \RuntimeException('Command name is not defined');
        }
        return static::COMMAND_NAME;
    }
}
