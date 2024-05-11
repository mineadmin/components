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
use Hyperf\Command\Command as Base;
use Mine\AppStore\Plugin;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class InitialCommand extends Base
{
    protected ?string $name = 'mine-extension:initial';

    protected string $description = 'MineAdmin Extended Store Initialization Command Line';

    public function __invoke(): void
    {
        $this->output->info('Start initialization');
        $this->output->info('Publishing multilingual documents');
        $publishPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'publish';
        $languages = BASE_PATH . '/storage/languages';
        FileSystem::copy($publishPath . '/trans/en.php', $languages . '/en/app-store.php');
        FileSystem::copy($publishPath . '/trans/zh-CN.php', $languages . '/zh_CN/app-store.php');
        $this->output->success('Language file published successfully');
        $this->output->info('Publishing Configuration Files');
        FileSystem::copy($publishPath . '/mine-extension.php', BASE_PATH . '/config/autoload/mine-extension.php');
        $this->output->success('Publishing Configuration File Succeeded');

        if (! file_exists(Plugin::PLUGIN_PATH)) {
            if (! mkdir($concurrentDirectory = Plugin::PLUGIN_PATH, 0755, true) && ! is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $binFile = file_get_contents(BASE_PATH . '/bin/hyperf.php');
            if (str_contains($binFile, 'Mine\AppStore\Plugin::init();')) {
                $binFile = str_replace('Hyperf\Di\ClassLoader::init();', '\\Mine\\AppStore\\Plugin::init();
    Hyperf\\Di\\ClassLoader::init();', $binFile);
                file_put_contents(BASE_PATH . '/bin/hyperf.php', $binFile);
                $this->output->success('Plugin initialization code added successfully.');
            }
            $this->output->success('Plugin directory created successfully');
        }

        $this->output->warning('
        接下来选择开发模式，
        默认是用 ./web 目录作为前端源码所在目录，
        也可以配置 mine-extension.php 配置文件
        手动指定前端源代码开发目录');
        $this->output->warning('
        Next, select the development mode.
        The default is to use . /web directory as the front-end source code directory.
        You can also configure the mine-extension.php configuration file
        Manually specify the front-end source code development directory');
    }

    protected function configure()
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether or not coverage is mandatory');
    }
}
