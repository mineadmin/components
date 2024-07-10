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

namespace Mine\AppStore\Command\Repository;

use Hyperf\Command\Annotation\Command;
use Mine\AppStore\Command\AbstractCommand;
use Mine\AppStore\Plugin;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ReleaseCommand extends AbstractCommand
{
    public const COMMAND_NAME = 'release';

    public function __invoke(): int
    {
        $path = $this->argument('path');
        $repository = $this->argument('repository');
        $version = $this->argument('version');
        $bin = dirname(__DIR__, 3) . '/bin';
        $repositoryPath = Plugin::PLUGIN_PREFIX . DIRECTORY_SEPARATOR . $path;
        $splitLinuxBin = $bin . DIRECTORY_SEPARATOR . 'release.sh';
        $basepath = BASE_PATH;
        $shell = <<<SHELL
cd {$basepath} && {$splitLinuxBin} {$repositoryPath} {$repository} {$version} {$bin}
SHELL;
        $result = System::exec($shell);
        if ($result['code'] !== 0) {
            $this->output->error('Fail' . $result['output']);
            return $result['code'];
        }
        $this->output->success('Push successfully');
        return 0;
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Plugin path');
        $this->addArgument('repository', InputArgument::REQUIRED, 'Git Repository path');
        $this->addArgument('version', InputArgument::REQUIRED, 'Repository version');
    }
}
