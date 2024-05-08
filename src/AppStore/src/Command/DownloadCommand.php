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
use Hyperf\Context\ApplicationContext;
use Mine\AppStore\Service\AppStoreService;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class DownloadCommand extends Base
{
    protected ?string $name = 'mine-extension:download';

    protected string $description = 'Download the specified remote plug-in file locally';

    public function __invoke()
    {
        $identifier = $this->input->getOption('identifier');
        $version = $this->input->getOption('version');
        $appStoreService = ApplicationContext::getContainer()->get(AppStoreService::class);
        $appStoreService->download($identifier, $version);
        $this->output->success('Plugin Downloaded Successfully');
    }

    protected function configure()
    {
        $this->addOption('identifier', 'n', InputOption::VALUE_REQUIRED, '必选,应用唯一标识符');
        $this->addOption('version', null, InputOption::VALUE_OPTIONAL, '应用版本号,默认latest', 'latest');
    }
}
