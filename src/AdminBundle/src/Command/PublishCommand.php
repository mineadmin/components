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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PublishCommand extends AbstractCommand
{
    public const PUBLISH_PATHS = [
        __DIR__ . '/../../database' => BASE_PATH,
        __DIR__ . '/../../languages' => BASE_PATH . 'storage/languages',
        __DIR__ . '/../../publish' => BASE_PATH . 'config',
    ];

    public function __invoke(): void
    {
        foreach (self::PUBLISH_PATHS as $source => $target) {
            $this->publish($source, $target);
        }
    }

    public function name(): string
    {
        return 'publish';
    }

    public function isForce(): bool
    {
        return (bool) $this->input->getOption('force');
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
            $targetFile = $this->getTargetPath($file, $target);
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

    public function copyFile(string $source, string $target): void
    {
        if (! copy($source, $target)) {
            $this->output->writeln(sprintf('<error>Failed to copy %s to %s</error>', $source, $target));
        }
    }

    public function getTargetPath(SplFileInfo $file, string $target): string
    {
        return $target . '/' . $file->getRelativePath();
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force publish files'],
        ];
    }
}
