<?php

declare(strict_types=1);

namespace Mine\AppStore\Support;

use Composer\Autoload\ClassLoader;
use RuntimeException;

class PluginAutoloader
{
    /**
     * @param  array<string, string|array<int, string>>  $mappings
     */
    public function addPsr4Mappings(array $mappings): void
    {
        $classLoader = $this->classLoader();

        foreach ($mappings as $namespace => $paths) {
            foreach ((array) $paths as $path) {
                $classLoader->addPsr4($namespace, $path);
            }
        }
    }

    private function classLoader(): ClassLoader
    {
        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader) && ($loader[0] ?? null) instanceof ClassLoader) {
                return $loader[0];
            }
        }

        throw new RuntimeException('Composer ClassLoader not found.');
    }
}
