<?php

namespace Xmo\AppStore\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as Base;
use Hyperf\Context\ApplicationContext;
use Symfony\Component\Console\Input\InputOption;
use Xmo\AppStore\Service\AppStoreService;

#[Command]
class ExtensionListCommand extends Base
{
    protected ?string $name = 'mine-extension:list';

    protected string $description = 'View a list of remote app store plugins';

    protected function configure()
    {
        $this->addOption('type','t',InputOption::VALUE_OPTIONAL,'Type of plugin to query');
        $this->addOption('title','title',InputOption::VALUE_OPTIONAL,'Plugin Title');
    }

    public function __invoke()
    {
        $params = [];
        $params['type'] = $this->input->getOption('type');
        if (empty($params['type'])){
             $type = 'all';
        }
        if ($title = $this->input->getOption('title')){
            $params['title'] = $title;
        }
        $appStoreService = ApplicationContext::getContainer()->get(AppStoreService::class);
        $result = $appStoreService->list($params);
        $headers = [
            'extensionName', 'description', 'author', 'homePage', 'status',
        ];
        $this->output->table($headers,$result);
    }
}