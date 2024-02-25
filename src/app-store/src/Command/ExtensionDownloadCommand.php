<?php

namespace Xmo\AppStore\Command;

use Hyperf\Command\Command as Base;
use Hyperf\Command\Annotation\Command;
use Hyperf\Context\ApplicationContext;
use Symfony\Component\Console\Input\InputOption;
use Xmo\AppStore\Service\AppStoreService;

#[Command]
class ExtensionDownloadCommand extends Base
{
    protected ?string $name = 'mine-extension:download';

    protected string $description = 'Download the specified remote plug-in file locally';

    protected function configure()
    {
        $this->addOption('name','n',InputOption::VALUE_REQUIRED,'Plug-in Name');
    }

    public function __invoke()
    {
        $name = $this->input->getOption('name');
        $appStoreService = ApplicationContext::getContainer()->get(AppStoreService::class);
        $appStoreService->download($name);
        $this->output->success('Plugin Downloaded Successfully');
    }
}