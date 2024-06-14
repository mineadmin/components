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

use Hyperf\Collection\Arr;
use Hyperf\Command\Annotation\Command;
use Hyperf\Support\Filesystem\Filesystem;
use Mine\AppStore\Plugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[Command]
class ScriptCommand extends AbstractCommand
{
    protected const COMMAND_NAME = 'script';

    protected bool $force = false;

    public function __construct(protected Filesystem $filesystem)
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $input = $this->input;
        /** @var SymfonyStyle $output */
        $output = $this->getOutput();
        $this->force = $input->getOption('force') !== false;
        $path = $input->getArgument('path');
        $show = $input->getOption('show') !== false;
        $id = $input->getOption('id');

        $provider = Arr::get(Plugin::read($path), 'composer.config');
        $config = (new $provider())();

        $publish = Arr::get($config, 'publish');
        if (empty($publish)) {
            $output->writeln(sprintf('<fg=red>No file can be published from plugin [%s].</>', $path));
            return AbstractCommand::FAILURE;
        }

        if ($show) {
            foreach ($publish as $item) {
                $out = '';
                foreach ($item as $key => $value) {
                    $out .= sprintf('%s: %s', $key, $value) . PHP_EOL;
                }
                $output->writeln(sprintf('<fg=green>%s</>', $out));
            }
            return AbstractCommand::SUCCESS;
        }

        if ($id) {
            $item = Arr::where($publish, function ($item) use ($id) {
                return $item['id'] == $id;
            });

            if (empty($item)) {
                $output->writeln(sprintf('<fg=red>No file can be published from [%s].</>', $id));
                return AbstractCommand::FAILURE;
            }
        }

        return $this->copy($path, $publish) ? AbstractCommand::SUCCESS : AbstractCommand::FAILURE;
    }

    protected function configure(): void
    {
        $this->setDescription('Publish any publishable configs from vendor plugins.')
            ->addArgument('path', InputArgument::REQUIRED, 'The plugin file you want run script.')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'The id of the plugin you want to publish.', null)
            ->addOption('show', 's', InputOption::VALUE_OPTIONAL, 'Show all plugins can be publish.', false)
            ->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'Overwrite any existing files', false);
    }

    protected function copy($path, $items): bool
    {
        foreach ($items as $item) {
            if (! isset($item['id'], $item['source'], $item['destination'])) {
                continue;
            }

            $id = $item['id'];
            $source = $item['source'];
            $destination = $item['destination'];

            if (! $this->force && $this->filesystem->exists($destination)) {
                $this->output->writeln(sprintf('<fg=red>[%s] already exists.</>', $destination));
                continue;
            }

            if (! $this->filesystem->exists($dirname = dirname($destination))) {
                $this->filesystem->makeDirectory($dirname, 0755, true);
            }

            if ($this->filesystem->isDirectory($source)) {
                $this->filesystem->copyDirectory($source, $destination);
            } else {
                $this->filesystem->copy($source, $destination);
            }

            $this->output->writeln(sprintf('<fg=green>[%s] publishes [%s] successfully.</>', $path, $id));
        }
        return true;
    }
}
