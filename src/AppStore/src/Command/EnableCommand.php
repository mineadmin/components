<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;
use Throwable;

class EnableCommand extends Command
{
    protected $signature = 'mine-plugin:enable {name : Plugin name, for example vendor/demo}';

    protected $description = 'Enable a local MineAdmin plugin.';

    public function handle(PluginLifecycleService $plugins): int
    {
        try {
            $plugins->enable((string) $this->argument('name'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Plugin [%s] enabled.', $this->argument('name')));

        return self::SUCCESS;
    }
}
