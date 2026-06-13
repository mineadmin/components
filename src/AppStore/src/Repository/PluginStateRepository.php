<?php

declare(strict_types=1);

namespace Mine\AppStore\Repository;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Mine\AppStore\Enums\PluginStatus;
use Throwable;

class PluginStateRepository
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $statePath
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        if (! $this->files->exists($this->statePath)) {
            return [];
        }

        $state = json_decode((string) $this->files->get($this->statePath), true);

        return is_array($state) ? $state : [];
    }

    public function status(string $name): PluginStatus
    {
        $status = $this->all()[$name]['status'] ?? null;

        return PluginStatus::tryFrom((string) $status) ?? PluginStatus::Discovered;
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    public function markEnabled(array $plugin): void
    {
        $state = $this->all();
        $name = (string) $plugin['name'];

        $state[$name] = array_merge($state[$name] ?? [], [
            'name' => $name,
            'version' => $plugin['version'] ?? null,
            'path' => $plugin['path'] ?? null,
            'provider' => $plugin['provider'] ?? null,
            'status' => PluginStatus::Enabled->value,
            'enabled_at' => Carbon::now()->toIso8601String(),
            'disabled_at' => null,
            'failed_at' => null,
            'last_error' => null,
        ]);

        $this->write($state);
    }

    public function markDisabled(string $name): void
    {
        $state = $this->all();

        $state[$name] = array_merge($state[$name] ?? ['name' => $name], [
            'status' => PluginStatus::Disabled->value,
            'disabled_at' => Carbon::now()->toIso8601String(),
        ]);

        $this->write($state);
    }

    /**
     * @param  array<string, mixed>  $plugin
     */
    public function markFailed(string $name, array $plugin, string $stage, Throwable $exception): void
    {
        $state = $this->all();
        $failedAt = Carbon::now()->toIso8601String();

        $state[$name] = array_merge($state[$name] ?? ['name' => $name], [
            'name' => $name,
            'version' => $plugin['version'] ?? ($state[$name]['version'] ?? null),
            'path' => $plugin['path'] ?? ($state[$name]['path'] ?? null),
            'provider' => $plugin['provider'] ?? ($state[$name]['provider'] ?? null),
            'status' => PluginStatus::Failed->value,
            'failed_at' => $failedAt,
            'last_error' => [
                'stage' => $stage,
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'failed_at' => $failedAt,
            ],
        ]);

        $this->write($state);
    }

    /**
     * @param  array<string, array<string, mixed>>  $state
     */
    private function write(array $state): void
    {
        $directory = dirname($this->statePath);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $temporaryPath = $this->statePath.'.tmp';
        $this->files->put($temporaryPath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->files->move($temporaryPath, $this->statePath);
    }
}
