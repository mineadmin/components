<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;

class ClearCommand extends Command
{
    protected $signature = 'mine-plugin:clear';

    protected $description = 'Clear the MineAdmin plugin runtime manifest.';

    public function handle(PluginLifecycleService $plugins): int
    {
        $plugins->clear();
        $this->info('Plugin runtime manifest cleared.');

        return self::SUCCESS;
    }
}
