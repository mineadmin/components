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

namespace Mine\Crontab\Command;

use Hyperf\Command\Command as Base;
use Hyperf\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

class CrontabMigrateCommand extends Base
{
    protected ?string $name = 'crontab:migrate';

    public function __construct(
        private readonly Migrator $migrator
    ) {
        parent::__construct();
    }

    public function __invoke()
    {
        $connection = $this->input->getOption('connection');
        if (empty($connection)) {
            $connection = 'default';
        }
        $migrator = $this->migrator;
        $migrator->setConnection($connection);
        $this->migrator
            ->setOutput(new NullOutput($this->input, $this->output))
            ->run(dirname(__DIR__, 2) . '/Database/Migrations');
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('connection', 'connection', InputOption::VALUE_OPTIONAL, 'connection name'),
        ];
    }
}
