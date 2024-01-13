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

namespace Mine\Command;

use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputOption;

#[\Hyperf\Command\Annotation\Command]
class MineGenServiceCommand extends Command
{
    public function handle()
    {
        $path = $this->input->getArgument('path');
        $model = $this->input->getOption('model');
        $mode = $this->input->getOption('mode');
        $searchParams = $this->input->getOption('search-params');
        $sortField = $this->input->getOption('sort-field');
    }

    protected function configure()
    {
        $this->setDescription('基于 Ast语法树解析 快速生成Service');
        $this->setDescription('快速生成 mapper');
        $this->setName('mine:gen-service');
        $this->addOption('name', 'n', InputOption::VALUE_NONE, 'build file name');
        $this->addOption('path', 'p', InputOption::VALUE_NONE, 'build path');
        $this->addOption('model', 'mo', InputOption::VALUE_REQUIRED, 'model class');
    }
}
