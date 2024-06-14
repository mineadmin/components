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

use Hyperf\Command\Annotation\Command;
use Mine\AppStore\Plugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class UninstallCommand extends AbstractCommand
{
    protected const COMMAND_NAME = 'uninstall';

    protected string $description = 'Uninstalling Plugin Commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $path = $this->input->getArgument('path');
        $yes = $this->input->getOption('yes');
        $pluginPath = BASE_PATH . '/plugin/' . $path;
        if (! is_dir($pluginPath)) {
            $this->output->error(sprintf('Plugin directory %s does not exist', $pluginPath));
            return AbstractCommand::FAILURE;
        }
        $info = Plugin::read($path);

        $headers = ['Extension name', 'author', 'description', 'homepage'];
        $rows[] = [
            $info['name'],
            is_string($info['author']) ? $info['author'] : ($info['author'][0]['name'] ?? '--'),
            $info['description'],
            $info['homepage'] ?? '--',
        ];
        $this->table($headers, $rows);
        $confirm = $yes ?: $this->confirm('Is the uninstallation cancelled?', true);
        if (! $confirm) {
            $this->output->success('Plugin uninstallation operation cancelled successfully');
            return AbstractCommand::SUCCESS;
        }
        Plugin::uninstall($path);
        $this->output->success(sprintf('Plugin %s uninstalled successfully', $pluginPath));
        return AbstractCommand::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Plug-in Catalog (relative path)')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'silent installation');
    }
}
