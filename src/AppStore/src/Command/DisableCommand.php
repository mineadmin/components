<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;
use Throwable;

class DisableCommand extends Command
{
    protected $signature = 'mine-plugin:disable {name : Plugin name, for example vendor/demo}';

    protected $description = 'Disable a local MineAdmin plugin.';

    public function handle(PluginLifecycleService $plugins): int
    {
        try {
            $plugins->disable((string) $this->argument('name'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Plugin [%s] disabled.', $this->argument('name')));

        return self::SUCCESS;
    }
}
