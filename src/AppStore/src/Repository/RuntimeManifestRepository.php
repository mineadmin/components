<?php

declare(strict_types=1);

namespace Mine\AppStore\Repository;

use Illuminate\Filesystem\Filesystem;

class RuntimeManifestRepository
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $manifestPath
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if (! $this->files->exists($this->manifestPath)) {
            return [];
        }

        $plugins = require $this->manifestPath;

        return is_array($plugins) ? $plugins : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $plugins
     */
    public function write(array $plugins): void
    {
        $directory = dirname($this->manifestPath);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $contents = "<?php\n\nreturn ".var_export(array_values($plugins), true).";\n";
        $temporaryPath = $this->manifestPath.'.tmp';

        $this->files->put($temporaryPath, $contents);
        $this->files->move($temporaryPath, $this->manifestPath);
    }

    public function remove(string $name): void
    {
        $this->write(array_values(array_filter(
            $this->all(),
            static fn (array $plugin): bool => ($plugin['name'] ?? null) !== $name
        )));
    }

    public function clear(): void
    {
        if ($this->files->exists($this->manifestPath)) {
            $this->files->delete($this->manifestPath);
        }
    }
}
