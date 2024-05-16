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
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class DownloadCommand extends Base
{
    protected ?string $name = 'mine-extension:download';

    protected string $description = 'Download the specified remote plug-in file locally';

    public function __invoke()
    {
        $identifier = $this->input->getArgument('identifier');
        [$space, $identifier] = explode('/', $identifier);
        $version = $this->input->getArgument('version');
        $appStoreService = ApplicationContext::getContainer()->get(AppStoreService::class);
        $appStoreService->download($space, $identifier, $version);
        $this->output->success('Plugin Downloaded Successfully');
    }

    protected function configure()
    {
        $this->addArgument('identifier', InputArgument::REQUIRED, 'Required, application unique identifier');
        $this->addArgument('version', InputArgument::OPTIONAL, 'Application version number, default latest', 'latest');
    }
}
