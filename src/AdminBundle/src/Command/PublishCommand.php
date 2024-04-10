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

namespace Mine\Admin\Bundle\Command;

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PublishCommand extends AbstractCommand
{
    public const PUBLISH_PATHS = [
        'publish' => [
            __DIR__ . '/../../publish' => BASE_PATH . 'config',
        ],
        'database' => [
            __DIR__ . '/../../database' => BASE_PATH,
        ],
        'languages' => [
            __DIR__ . '/../../languages' => BASE_PATH . 'storage/languages',
        ],
    ];

    public function __invoke(): void
    {
        $inputTag = $this->getInputTag();
        foreach (self::PUBLISH_PATHS as $tag => $tagSource) {
            if (in_array($inputTag, [$tag, 'all'], true)) {
                $this->output->writeln(sprintf('Tag %s publish', $tag));
                foreach ($tagSource as $source => $target) {
                    $this->publish($source, $target);
                }
            }
        }
    }

    public function getInputTag(): string
    {
        if (! $this->hasOption('tag')) {
            return 'all';
        }
        return $this->input->getOption('tag');
    }

    public function name(): string
    {
        return 'publish';
    }

    public function isForce(): bool
    {
        return $this->input->hasOption('force') && $this->input->getOption('force');
    }

    public function publish(string $source, string $target): void
    {
        /**
         * 把 source 目录下的文件发布到 target 目录下。
         * 如果存在的话判断下是否是强制发布。
         * 如果是强制发布，则直接覆盖。
         * 如果不是强制发布，则判断下文件是否是修改过的。
         */
        $finder = Finder::create()->files()->in($source);
        /**
         * @var SplFileInfo $file
         */
        foreach ($finder as $file) {
            $targetFile = $this->getTargetPath($file, $target) . DIRECTORY_SEPARATOR . $file->getBasename();
            if (is_file($targetFile) && ! is_dir($target)) {
                // 添加了对目标路径是否有效的检查
                $this->output->writeln(sprintf('<error>Invalid target: %s</error>', $targetFile));
                continue;
            }

            if (is_file($targetFile) && ! $this->isForce()) {
                $this->output->writeln(sprintf('<info>Skipped</info>: %s', $targetFile));
                continue;
            }

            if (is_file($targetFile) && $this->isForce()) {
                $this->output->writeln(sprintf('<info>Overwrite</info>: %s', $targetFile));
                $this->copyFile($file, $targetFile);
            }
            if (! is_file($targetFile)) {
                $this->output->writeln(sprintf('<info>Publish</info>: %s', $targetFile));
                $this->copyFile($file, $targetFile);
            }
        }
    }

    public function copyFile(SplFileInfo $source, string $target): void
    {
        FileSystem::copy($source->getRealPath(), $target);
    }

    public function getTargetPath(SplFileInfo $file, string $target): string
    {
        return $target . '/' . $file->getRelativePath();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force publish files');
        $this->addOption('tag', 'tag', InputOption::VALUE_OPTIONAL, 'Publish tag file');
    }
}
