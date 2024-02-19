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
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ExtensionInitialCommand extends Base
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
