<?php

declare(strict_types=1);

namespace Mine\AppStore\Command;

use Illuminate\Console\Command;
use Mine\AppStore\Service\PluginLifecycleService;

class DoctorCommand extends Command
{
    protected $signature = 'mine-plugin:doctor {name? : Optional plugin name}';

    protected $description = 'Diagnose local MineAdmin plugin manifests and lifecycle state.';

    public function handle(PluginLifecycleService $plugins): int
    {
        $diagnostics = $plugins->doctor($this->argument('name') ? (string) $this->argument('name') : null);

        $this->table(
            ['Name', 'Status', 'Path', 'Errors'],
            array_map(static function (array $plugin): array {
                return [
                    $plugin['name'] ?? '--',
                    $plugin['status'] ?? '--',
                    $plugin['path'] ?? '--',
                    implode(PHP_EOL, $plugin['errors'] ?? []),
                ];
            }, $diagnostics)
        );

        return self::SUCCESS;
    }
}
