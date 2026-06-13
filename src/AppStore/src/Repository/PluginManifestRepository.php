<?php

declare(strict_types=1);

namespace Mine\AppStore\Repository;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mine\AppStore\Enums\PluginStatus;

class PluginManifestRepository
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $pluginsPath
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function discover(): array
    {
        $pluginsRoot = realpath($this->pluginsPath);

        if ($pluginsRoot === false || ! is_dir($pluginsRoot)) {
            return [];
        }

        $manifestPaths = $this->files->glob($pluginsRoot.'/*/*/mine.json') ?: [];
        sort($manifestPaths);

        return array_map(fn (string $manifestPath): array => $this->readManifest($manifestPath, $pluginsRoot), $manifestPaths);
    }

    /**
     * @return array<string, mixed>
     */
    private function readManifest(string $manifestPath, string $pluginsRoot): array
    {
        $pluginPath = dirname($manifestPath);
        $errors = [];

        try {
            $manifest = json_decode((string) $this->files->get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $manifest = [];
            $errors[] = sprintf('mine.json contains invalid JSON: %s', $exception->getMessage());
        }

        if (! is_array($manifest)) {
            $manifest = [];
            $errors[] = 'mine.json must contain a JSON object.';
        }

        $relativePath = trim(str_replace($pluginsRoot, '', $pluginPath), DIRECTORY_SEPARATOR);
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        $name = (string) ($manifest['name'] ?? $relativePath);
        $provider = (string) ($manifest['provider'] ?? '');
        $autoload = $manifest['autoload']['psr-4'] ?? [];
        $require = $manifest['require'] ?? [];

        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*\/[A-Za-z0-9][A-Za-z0-9_-]*$/', $name)) {
            $errors[] = 'Plugin name must use vendor/name format.';
        }

        if ($relativePath !== $name) {
            $errors[] = sprintf('Plugin directory [%s] must match manifest name [%s].', $relativePath, $name);
        }

        if ($provider === '') {
            $errors[] = 'Provider is required.';
        }

        if (! is_array($autoload) || $autoload === []) {
            $errors[] = 'autoload.psr-4 must define at least one namespace mapping.';
            $autoload = [];
        }

        $normalizedAutoload = [];

        foreach ($autoload as $namespace => $path) {
            if (! is_string($namespace) || ! str_ends_with($namespace, '\\')) {
                $errors[] = 'PSR-4 namespace must be a string ending with a backslash.';

                continue;
            }

            if (! is_string($path)) {
                $errors[] = sprintf('PSR-4 path for namespace [%s] must be a string.', $namespace);

                continue;
            }

            $absolutePath = $this->normalizePluginRelativePath($pluginPath, $path);

            if ($absolutePath === null) {
                $errors[] = sprintf('PSR-4 path [%s] for namespace [%s] is invalid or escapes the plugin directory.', $path, $namespace);

                continue;
            }

            $normalizedAutoload[$namespace] = $absolutePath;
        }

        $requiredPlugins = $require['plugins'] ?? [];

        if (! is_array($requiredPlugins)) {
            $errors[] = 'require.plugins must be an array.';
            $requiredPlugins = [];
        }

        foreach ($requiredPlugins as $requiredPlugin) {
            if (! is_string($requiredPlugin) || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*\/[A-Za-z0-9][A-Za-z0-9_-]*$/', $requiredPlugin)) {
                $errors[] = 'Each plugin dependency must use vendor/name format.';
            }
        }

        return [
            'name' => $name,
            'path' => $pluginPath,
            'version' => (string) ($manifest['version'] ?? '0.0.0'),
            'description' => (string) ($manifest['description'] ?? ''),
            'author' => $manifest['author'] ?? null,
            'provider' => $provider,
            'autoload' => [
                'psr-4' => $normalizedAutoload,
            ],
            'require' => [
                'plugins' => array_values(array_filter($requiredPlugins, is_string(...))),
                'php' => $require['php'] ?? null,
                'laravel' => $require['laravel'] ?? null,
            ],
            'status' => $errors === [] ? PluginStatus::Discovered->value : PluginStatus::Invalid->value,
            'errors' => $errors,
            'manifest_path' => $manifestPath,
            'namespace' => $this->defaultNamespace($name),
        ];
    }

    private function normalizePluginRelativePath(string $pluginPath, string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '..')) {
            return null;
        }

        $pluginRoot = realpath($pluginPath);
        $candidate = realpath($pluginPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

        if ($pluginRoot === false || $candidate === false || ! is_dir($candidate)) {
            return null;
        }

        if ($candidate !== $pluginRoot && ! str_starts_with($candidate, $pluginRoot.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $candidate;
    }

    private function defaultNamespace(string $name): string
    {
        [$vendor, $plugin] = array_pad(explode('/', $name, 2), 2, '');

        return sprintf('Plugin\\%s\\%s', Str::studly($vendor), Str::studly($plugin));
    }
}
