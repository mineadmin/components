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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Xmo\AppStore\Plugin;
use Xmo\AppStore\Service\PluginService;

#[Command]
class ExtensionUninstallCommand extends Base
{
    protected ?string $name = 'mine-extension:uninstall';

    protected string $description = 'Uninstalling Plugin Commands';

    public function __construct(
        private readonly PluginService $pluginService
    ) {
        parent::__construct();
    }

    public function __invoke()
    {
        $path = $this->input->getArgument('path');
        $yes = $this->input->getOption('yes');
        $pluginPath = BASE_PATH . '/plugin/' . $path;
        if (! file_exists($pluginPath)) {
            $this->output->error(sprintf('Plugin directory %s does not exist', $pluginPath));
            return;
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
            return;
        }
        Plugin::uninstall($path);
        $this->output->success(sprintf('Plugin %s uninstalled successfully', $pluginPath));
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Plug-in Catalog (relative path)');
        $this->addOption('yes', 'y', InputOption::VALUE_NONE, 'silent installation');
    }
}
