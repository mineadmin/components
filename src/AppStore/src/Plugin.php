<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\AppStore;

use Composer\InstalledVersions;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Database\Seeders\Seed;
use Hyperf\Support\Composer;
use Mine\AppStore\Exception\PluginNotFoundException;
use Mine\AppStore\Packer\PackerFactory;
use Mine\AppStore\Packer\PackerInterface;
use Mine\AppStore\Utils\FileSystemUtils;
use Swoole\Coroutine\System;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Plugin
{
    /**
     * File flags for successful plugin installation.
     */
    public const INSTALL_LOCK_FILE = 'install.lock';

    /**
     * Plugin root directory.
     */
    public const PLUGIN_PATH = BASE_PATH . '/plugin';

    private static array $mineJsonPaths = [];

    public static function getPacker(): PackerInterface
    {
        return (new PackerFactory())->get();
    }

    public static function init(): void
    {
        // Initialize to load all plugin information into memory
        $mineJsons = self::getPluginJsonPaths();

        foreach ($mineJsons as $mine) {
            // If the plugin identifies itself as installed, load the psr4 psr0 classMap in memory
            $mineInfo = self::read($mine->getRelativePath());

            if (file_exists($mine->getPath() . '/' . self::INSTALL_LOCK_FILE)) {
                self::loadPlugin($mineInfo, $mine);
            }
        }
    }

    /**
     * Check if the given file belongs to a qualified and valid plug-in.
     */
    public static function checkPlugin(\SplFileInfo $mineJson): bool
    {
        $info = self::read($mineJson->getRealPath());
        $rules = [
            'name' => 'required|string',
            'description' => 'required|string',
            'author' => 'required|string',
            'composer' => 'required|array',
            'package' => 'array',
        ];
        // todo 这里对插件信息进行验证
        return true;
    }

    /**
     * Get information about all local plugins.
     * @return SplFileInfo[]
     */
    public static function getPluginJsonPaths(): array
    {
        if (self::$mineJsonPaths) {
            return self::$mineJsonPaths;
        }
        $mines = Finder::create()
            ->in(self::PLUGIN_PATH)
            ->name('mine.json')
            ->sortByChangedTime();
        foreach ($mines as $jsonFile) {
            self::$mineJsonPaths[] = $jsonFile;
        }
        return self::$mineJsonPaths;
    }

    /**
     * Query plugin information based on a given catalog.
     * @return array<string,mixed>
     * @throws PluginNotFoundException
     */
    public static function read(string $path): array
    {
        $jsonPaths = self::getPluginJsonPaths();
        foreach ($jsonPaths as $jsonPath) {
            if ($jsonPath->getRelativePath() === $path) {
                $info = self::getPacker()->unpack(file_get_contents($jsonPath->getRealPath()));
                $info['status'] = is_file($jsonPath->getPath() . '/' . self::INSTALL_LOCK_FILE);
                return $info;
            }
        }
        throw new PluginNotFoundException($path);
    }

    /**
     * @throws PluginNotFoundException
     */
    public static function getSplFile(string $path): SplFileInfo
    {
        $jsonPaths = self::getPluginJsonPaths();
        foreach ($jsonPaths as $jsonPath) {
            if ($jsonPath->getRelativePath() === $path) {
                return $jsonPath;
            }
        }
        throw new PluginNotFoundException($path);
    }

    /**
     * Detects if the given plugin exists and is installed.
     */
    public static function exists(string $name): bool
    {
        $jsonPaths = self::getPluginJsonPaths();
        foreach ($jsonPaths as $jsonPath) {
            $info = self::read($jsonPath->getRelativePath());
            if ($info['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    public static function forceRefreshJsonPath(): void
    {
        self::$mineJsonPaths = [];
    }

    /**
     * Install the plugin according to the given directory.
     */
    public static function install(string $path): void
    {
        $info = self::read($path);
        $splFile = self::getSplFile($path);

        self::loadPlugin($info, $splFile);
        $pluginPath = self::PLUGIN_PATH . '/' . $path;
        if ($info['status']) {
            throw new \RuntimeException(
                'The given directory detects an installation and terminates the installation operation'
            );
        }
        // Performs a check on plugin dependencies. Determine if the plugin also depends on other plugins
        if (! empty($info['require']) && ! is_array($info['require'])) {
            throw new \RuntimeException('Plugin dependency format error');
        }
        if (! empty($info['require'])) {
            $pluginRequires = $info['require'];
            foreach ($pluginRequires as $require) {
                if (! self::exists($require)) {
                    throw new \RuntimeException(
                        sprintf(
                            'Plugin %s depends on plugin %s, but the dependency is not installed',
                            $info['name'],
                            $require
                        )
                    );
                }
            }
        }
        // Handling composer dependencies
        if (! empty($info['composer']['require'])) {
            $requires = $info['composer']['require'];
            $composerBin = self::getConfig('composer.bin', 'composer');
            $checkCmd = System::exec(sprintf('%s --version', $composerBin));
            if (($checkCmd['code'] ?? 0) !== 0) {
                throw new \RuntimeException(sprintf('Composer command error, details:%s', $checkCmd['output'] ?? '--'));
            }

            $execList[] = sprintf('cd %s &&', BASE_PATH);
            $packageList = [];
            foreach ($requires as $package => $version) {
                if (! InstalledVersions::isInstalled($package)) {
                    $packageList[] = sprintf('%s:%s ', $package, $version);
                }
            }
            if (! empty($packageList)) {
                $requireCmd = sprintf('%s require %s', $composerBin, implode(' ', $packageList));
                $execList[] = $requireCmd;
            }
            foreach ($execList as $cmd){
                $result = System::exec($cmd);
                if ($result['code'] !== 0 && ! empty($result['ouput'])) {
                    throw new \RuntimeException(sprintf('Failed to execute composer require command, details:%s', $result['output'] ?? '--'));
                }
            }
        }

        // run script
        if (! empty($info['composer']['script'])) {
            $scripts = $info['composer']['script'];
            $runScriptCmd = sprintf('cd %s &&', BASE_PATH);
            foreach ($scripts as $name => $script) {
                $result = System::exec(sprintf('%s %s', $runScriptCmd, $script));
                if ($result['code'] !== 0 && ! empty($result['ouput'])) {
                    throw new \RuntimeException(sprintf('Failed to execute composer script command, details:%s', $result['output'] ?? '--'));
                }
            }
        }

        // check is run publish command
        if (! empty($info['composer']['config'])) {
            $composerConfig = (new $info['composer']['config']())();
            if (! empty($composerConfig['publish'])) {
                System::exec(sprintf('cd %s && php bin/hyperf.php mine-extension:script %s', BASE_PATH, $path));
            }
        }

        $frontDirectory = self::getConfig('front_directory', BASE_PATH . '/web');

        if (! empty($info['installScript']) && class_exists($info['installScript'])) {
            $installScript = ApplicationContext::getContainer()->make($info['installScript']);
            $installScript();
        }

        // Handling front-end dependency information
        if (! empty($info['package']['dependencies'])) {
            $frontBin = self::getConfig('front-tool');
            $dependencies = $info['package']['dependencies'];
            if (! file_exists($frontDirectory . '/package.json')) {
                throw new \RuntimeException(sprintf('Specified frontend directory %s package.json not found', $frontDirectory));
            }
            $packageJson = self::getPacker()->unpack(file_get_contents($frontDirectory . '/package.json'));
            $frontDependencies = array_keys($packageJson['dependencies'] ?? []);
            $type = $frontBin['type'] ?? 'npm';
            $front = $frontBin['bin'] ?? 'npm';
            $checkCmd = System::exec(sprintf('%s ', $type));
            if ($checkCmd['code'] !== 0 && ! empty($result['ouput'])) {
                throw new \RuntimeException(sprintf('An error occurred executing the command %s, details:%s', $type, $checkCmd['output']));
            }
            $installCmd = match ($type) {
                'npm', 'pnpm' => 'install',
                'yarn' => 'add',
                default => null
            };
            if ($installCmd === null) {
                throw new \RuntimeException('Front-end tool type is not recognizable npm,yarn,pnpm');
            }
            $cmdBody = sprintf('cd %s && %s %s ', $frontDirectory, $front, $installCmd);
            foreach ($dependencies as $package => $version) {
                if (in_array($package, $frontDependencies, true)) {
                    throw new \RuntimeException(sprintf('Plugin %s depends on %s, but the dependency already exists in the project.', $info['name'], $package));
                }
                $cmdBody .= sprintf('%s@%s ', $package, $version);
            }
            $result = System::exec($cmdBody);
            if ($result['code'] !== 0 && ! empty($result['ouput'])) {
                throw new \RuntimeException(sprintf('Merge front-end dependency module error, details %s', $result['output'] ?? '--'));
            }
        }

        // Handling database migration
        $migrator = ApplicationContext::getContainer()->get(Migrator::class);
        $seeder = ApplicationContext::getContainer()->get(Seed::class);

        // Perform migration
        $migrator->run($pluginPath . '/Database/Migrations');
        // Perform Data Filling
        $seeder->run($pluginPath . '/Database/Seeders');
        // If the plugin exists in the web directory, perform the migration of the front-end files
        if (file_exists($pluginPath . '/web')) {
            $finder = Finder::create()
                ->files()
                ->in($pluginPath . '/web');
            foreach ($finder as $file) {
                /**
                 * @var SplFileInfo $file
                 */
                $relativeFilePath = $file->getRelativePathname();
                FileSystemUtils::copy($pluginPath . '/web/' . $relativeFilePath, $frontDirectory . $relativeFilePath);
            }
        }

        file_put_contents($pluginPath . '/' . self::INSTALL_LOCK_FILE, 1);
    }

    public static function uninstall(string $path): void
    {
        $info = self::read($path);
        $pluginPath = self::PLUGIN_PATH . '/' . $path;
        if ($info === null || ! $info['status']) {
            throw new \RuntimeException(
                'No installation behavior was detected for this plugin, and uninstallation could not be performed'
            );
        }
        if (! empty($info['uninstallScript']) && class_exists($info['uninstallScript'])) {
            $uninstallScript = ApplicationContext::getContainer()->make($info['uninstallScript']);
            $uninstallScript();
        }
        if (! empty($info['composer']['require'])) {
            $requires = $info['composer']['require'];
            $composerBin = self::getConfig('composer.bin', 'composer');
            $checkCmd = System::exec(sprintf('%s --version', $composerBin));
            if (($checkCmd['code'] ?? 0) !== 0) {
                throw new \RuntimeException(sprintf('Composer command error, details:%s', $checkCmd['output'] ?? '--'));
            }
            $cmdBody = sprintf('cd %s &&', BASE_PATH);
            $cmdBody .= sprintf('%s remove ', $composerBin);
            foreach ($requires as $package => $version) {
                if (InstalledVersions::isInstalled($package)) {
                    $cmdBody .= sprintf('%s:%s ', $package, $version);
                }
            }
            $cmdBody .= sprintf('-vvv');
            $result = System::exec($cmdBody);
            if ($result['code'] !== 0 && ! empty($result['ouput'])) {
                throw new \RuntimeException(sprintf('Failed to execute composer require command, details:%s', $result['output'] ?? '--'));
            }
        }

        $frontDirectory = self::getConfig('front_directory', BASE_PATH . '/web');

        // Handling front-end dependency information
        if (! empty($info['package']['dependencies'])) {
            $frontBin = self::getConfig('front-tool');
            $dependencies = $info['package']['dependencies'];
            if (! file_exists($frontDirectory . '/package.json')) {
                throw new \RuntimeException(sprintf('Specified frontend directory %s package.json not found', $frontDirectory));
            }
            $packageJson = self::getPacker()->unpack(file_get_contents($frontDirectory . '/package.json'));
            $frontDependencies = array_keys($packageJson['dependencies'] ?? []);
            $type = $frontBin['type'] ?? 'npm';
            $front = $frontBin['bin'] ?? 'npm';
            $checkCmd = System::exec(sprintf('%s ', $type));
            if ($checkCmd['code'] !== 0 && ! empty($result['ouput'])) {
                throw new \RuntimeException(sprintf('An error occurred executing the command %s, details:%s', $type, $checkCmd['output']));
            }
            $installCmd = match ($type) {
                'npm', 'pnpm' => 'uninstall',
                'yarn' => 'remove',
                default => null
            };
            if ($installCmd === null) {
                throw new \RuntimeException('Front-end tool type is not recognizable npm,yarn,pnpm');
            }
            $cmdBody = sprintf('cd %s && %s %s ', $frontDirectory, $front, $installCmd);
            foreach ($dependencies as $package => $version) {
                if (! in_array($package, $frontDependencies, true)) {
                    throw new \RuntimeException(sprintf('Plugin %s depends on %s,But the dependency information is not found in this project', $info['name'], $package));
                }
                $cmdBody .= sprintf('%s@%s ', $package, $version);
            }
            $result = System::exec($cmdBody);
            if ($result['code'] !== 0 && ! empty($result['ouput'])) {
                throw new \RuntimeException(sprintf('Merge front-end dependency module error, details %s', $result['output'] ?? '--'));
            }
        }

        // Handling database migration
        $migrator = ApplicationContext::getContainer()->get(Migrator::class);

        // Perform migration rollback
        $migrator->rollback($pluginPath . '/Database/Migrations');
        // If the plugin exists in the web directory, perform the migration of the front-end files
        if (file_exists($pluginPath . '/web')) {
            $finder = Finder::create()
                ->files()
                ->in($pluginPath . '/web');
            foreach ($finder as $file) {
                /**
                 * @var SplFileInfo $file
                 */
                $relativeFilePath = $file->getRelativePathname();
                FileSystemUtils::recovery($relativeFilePath, $frontDirectory);
            }
        }

        unlink($pluginPath . '/' . self::INSTALL_LOCK_FILE);
    }

    public static function getConfig(string $key, mixed $default = null): mixed
    {
        return ApplicationContext::getContainer()
            ->get(ConfigInterface::class)
            ->get('mine-extension.' . $key, $default);
    }

    private static function loadPlugin(array $mineInfo, SplFileInfo $mine): void
    {
        $loader = Composer::getLoader();
        // psr-4
        if (! empty($mineInfo['composer']['psr-4'])) {
            foreach ($mineInfo['composer']['psr-4'] as $namespace => $src) {
                $src = realpath($mine->getPath() . '/' . $src);
                $loader->addPsr4($namespace, $src);
            }
        }

        // files
        if (! empty($mineInfo['composer']['files'])) {
            foreach ($mineInfo['composer']['files'] as $file) {
                require_once $mine->getPath() . '/' . $file;
            }
        }

        // classMap
        if (! empty($mineInfo['composer']['classMap'])) {
            $loader->addClassMap($mineInfo['composer']['classMap']);
        }

        // self::checkPlugin($mine);
    }
}
