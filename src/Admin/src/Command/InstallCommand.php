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

use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class InstallCommand extends AbstractCommand
{
    public function name(): string
    {
        return 'install';
    }

    public function handle(): void
    {
        $publishable = $this->getPublishable();
        foreach ($publishable as $vendor) {
            $this->call('vendor:publish', [
                'package' => $vendor,
                '--force' => true,
            ]);
        }

        $this->call('mine-extension:initial');

        // If the user does not want to overwrite the installation, return
        if (! $this->input->getOption('force')) {
            $this->output->success('Please use the --force option to overwrite the installation');
            return;
        }
        $this->output->success('Published configuration file');

        $this->call('migrate', [
            '--realpath' => dirname(__DIR__, 2) . '/Migration/Databases',
            '--database' => $this->input->getOption('database'),
        ]);

        $this->output->success('Migrated database');

        $this->call('db:seed', [
            '--realpath' => dirname(__DIR__, 2) . '/Migration/Seeder',
            '--database' => $this->input->getOption('database'),
            '--force' => true,
        ]);
        $this->output->success('Seeded database');
    }

    protected function configure(): void
    {
        $this->setDescription('Install MineAdmin');
        $this->setHelp('Install MineAdmin');
    }

    /**
     * Get the providers that should be published.
     */
    protected function getPublishable(): array
    {
        return [
            'mineadmin/security-http',
            'mineadmin/security-access',
            'mineadmin/http-server',
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite Install MineAdmin'],
            ['database', 'd', InputOption::VALUE_OPTIONAL, 'The name of the database', 'default'],
        ];
    }
}
