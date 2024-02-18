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
use Xmo\AppStore\Service\AppStoreService;

#[Command]
class ExtensionLocalListCommand extends Base
{
    protected ?string $name = 'mine-extension:local-list';

    protected string $description = 'List all locally installed extensions(列出本地所有已经安装的扩展)';

    public function __invoke()
    {
        $service = ApplicationContext::getContainer()->get(AppStoreService::class);
        $list = $service->getLocalExtensions();
        $headers = [
            'extensionName', 'description', 'author', 'homePage', 'status',
        ];
        $rows = [];
        foreach ($list as $info) {
            $current = [];
            $current[] = $info['name'];
            $current[] = $info['description'];
            if (is_string($info['author'])) {
                $current[] = $info['author'];
            } else {
                $current[] = $info['author'][0]['name'] ?? '--';
            }
            $current[] = $info['homePage'] ?? '--';
            $current[] = $info['status'] ? 'installed' : 'uninstalled';
            $rows[] = $current;
        }
        $this->table($headers, $rows);
    }
}
