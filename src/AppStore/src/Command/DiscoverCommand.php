<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;

class DiscoverCommand extends Command
{
    protected $signature = 'mine-plugin:discover {--json : Output raw JSON}';

    protected $description = 'Discover local MineAdmin plugins.';

    public function handle(PluginLifecycleService $plugins): int
    {
        $discoveredPlugins = $plugins->discover();

        if ((bool) $this->option('json')) {
            $this->line(json_encode($discoveredPlugins, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Version', 'Provider', 'Status', 'Errors'],
            array_map(static function (array $plugin): array {
                return [
                    $plugin['name'] ?? '--',
                    $plugin['version'] ?? '--',
                    $plugin['provider'] ?? '--',
                    $plugin['status'] ?? '--',
                    implode(PHP_EOL, $plugin['errors'] ?? []),
                ];
            }, $discoveredPlugins)
        );

        return self::SUCCESS;
    }
}
