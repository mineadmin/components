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
class PushCommand extends AbstractCommand
{
    protected const COMMAND_NAME = 'push';

    public function __invoke(): int
    {
        $path = $this->input->getArgument('path');
        $repository = $this->input->getArgument('repository');
        $bin = dirname(__DIR__, 3) . '/bin';
        $repositoryPath = Plugin::PLUGIN_PREFIX . DIRECTORY_SEPARATOR . $path;
        $splitLinuxBin = $bin . DIRECTORY_SEPARATOR . 'split-linux.sh';
        $basepath = BASE_PATH;
        $shell = <<<SHELL
cd {$basepath} && {$splitLinuxBin} {$repositoryPath} {$repository} {$bin}
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
        $this->setDescription('Quickly perform plugin packaging and deployment processing.');
        $this->addArgument('path', InputArgument::REQUIRED, 'Plugin path');
        $this->addArgument('repository', InputArgument::REQUIRED, 'Git Repository path');
    }
}
