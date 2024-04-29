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
use Hyperf\Context\ApplicationContext;
use Symfony\Component\Console\Input\InputOption;
use Xmo\AppStore\Service\AppStoreService;

#[Command]
class ListCommand extends Base
{
    protected ?string $name = 'mine-extension:list';

    protected string $description = 'View a list of remote app store plugins';

    public function __invoke()
    {
        $params = [];
        $params['type'] = $this->input->getOption('type');
        if (empty($params['type'])) {
            $type = 'all';
        }
        if ($title = $this->input->getOption('title')) {
            $params['title'] = $title;
        }
        $appStoreService = ApplicationContext::getContainer()->get(AppStoreService::class);
        $result = $appStoreService->list($params)['data']['list'] ?? [];
        $headers = [
            'extensionName', 'description', 'author', 'homePage', 'status',
        ];
        $this->output->table($headers, $result);
    }

    protected function configure()
    {
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of plugin to query');
        $this->addOption('title', 'title', InputOption::VALUE_OPTIONAL, 'Plugin Title');
    }
}
