<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;
use Throwable;

class CacheCommand extends Command
{
    protected $signature = 'mine-plugin:cache';

    protected $description = 'Rebuild the MineAdmin plugin runtime manifest.';

    public function handle(PluginLifecycleService $plugins): int
    {
        try {
            $enabledPlugins = $plugins->cache();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Cached %d enabled plugin(s).', count($enabledPlugins)));

        return self::SUCCESS;
    }
}
