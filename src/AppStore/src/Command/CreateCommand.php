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

namespace Mine\AppStore\Command;

use Hyperf\Codec\Json;
use Hyperf\Command\Annotation\Command;
use Hyperf\Stringable\Str;
use Mine\AppStore\Enums\PluginTypeEnum;
use Mine\AppStore\Plugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class CreateCommand extends AbstractCommand
{
    protected const COMMAND_NAME = 'create';

    protected string $description = 'Creating Plug-ins';

    public function __invoke(): int
    {
        $path = $this->input->getArgument('path');
        $type = $this->input->getOption('type') ?? 'mix';
        $type = PluginTypeEnum::fromValue($type);
        if ($type === null) {
            $this->output->error('Plugin type is empty');
            return AbstractCommand::FAILURE;
        }

        $pluginPath = Plugin::PLUGIN_PATH . '/' . $path;
        if (file_exists($pluginPath)) {
            $this->output->error(\sprintf('Plugin directory %s already exists', $path));
            return AbstractCommand::FAILURE;
        }

        $path = str_replace('\\', '/', trim((string) $path));
        if (! preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9_-]*[A-Za-z0-9])?\/[A-Za-z0-9](?:[A-Za-z0-9_-]*[A-Za-z0-9])?$/', $path) || str_contains($path, '..')) {
            $this->output->error('Invalid plugin path. Use: organization/plugin-name (letters or digits, dash/underscore allowed, no dot).');
            return AbstractCommand::FAILURE;
        }

        $createDirectors = [
            $pluginPath, $pluginPath . '/src', $pluginPath . '/Database', $pluginPath . '/Database/Migrations', $pluginPath . '/Database/Seeders', $pluginPath . '/web',
        ];
        foreach ($createDirectors as $directory) {
            if (! mkdir($directory, 0o755, true) && ! is_dir($directory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $directory));
            }
        }

        $this->createMineJson($pluginPath, $type);
        return AbstractCommand::SUCCESS;
    }

    public function createNamespace(string $path): string
    {
        $pluginPath = Str::replace(Plugin::PLUGIN_PATH . '/', '', $path);
        [$orgName, $extName] = explode('/', $pluginPath);
        return 'Plugin\\' . Str::studly($orgName) . '\\' . Str::studly($extName);
    }

    public function createMineJson(string $path, PluginTypeEnum $pluginType): void
    {
        $pluginPath = Str::replace(Plugin::PLUGIN_PATH . '/', '', $path);

        $output = new \stdClass();
        $output->name = $pluginPath;
        $output->version = '1.0.0';
        $output->type = $pluginType->value;
        $output->description = $this->input->getOption('description') ?: 'This is a sample plugin';
        $author = $this->input->getOption('author') ?: 'demo';
        $output->author = [
            [
                'name' => $author,
            ],
        ];
        if ($pluginType === PluginTypeEnum::Backend || $pluginType === PluginTypeEnum::Mix) {
            $namespace = $this->createNamespace($path);

            $this->createInstallScript($namespace, $path);
            $this->createUninstallScript($namespace, $path);
            $this->createConfigProvider($namespace, $path);
            $this->createViewScript($namespace, $path);
            $output->composer = [
                'require' => [],
                'psr-4' => [
                    $namespace . '\\' => 'src',
                ],
                'installScript' => $namespace . '\InstallScript',
                'uninstallScript' => $namespace . '\UninstallScript',
                'config' => $namespace . '\ConfigProvider',
            ];
        }

        if ($pluginType === PluginTypeEnum::Mix || $pluginType === PluginTypeEnum::Frond) {
            $output->package = [
                'dependencies' => [
                ],
            ];
        }
        $output = Json::encode($output, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT);
        file_put_contents($path . '/mine.json', $output);
        $this->output->success(\sprintf('%s 创建成功', $path . '/mine.json'));
    }

    public function createInstallScript(string $namespace, string $path): void
    {
        $installScript = $this->buildStub('InstallScript', compact('namespace'));
        $installScriptPath = $path . '/src/InstallScript.php';
        file_put_contents($installScriptPath, $installScript);
        $this->output->success(\sprintf('%s Created Successfully', $installScriptPath));
    }

    public function buildStub(string $stub, array $replace): string
    {
        $stubPath = $this->getStubDirectory() . '/' . $stub . '.stub';
        if (! file_exists($stubPath)) {
            throw new \RuntimeException(\sprintf('File %s does not exist', $stubPath));
        }
        $stubBody = file_get_contents($stubPath);
        foreach ($replace as $key => $value) {
            $stubBody = str_replace('%' . $key . '%', $value, $stubBody);
        }
        return $stubBody;
    }

    public function getStubDirectory(): string
    {
        return realpath(__DIR__) . '/Stub';
    }

    public function createUninstallScript(string $namespace, string $path): void
    {
        $installScript = $this->buildStub('UninstallScript', compact('namespace'));
        $installScriptPath = $path . '/src/UninstallScript.php';
        file_put_contents($installScriptPath, $installScript);
        $this->output->success(\sprintf('%s Created Successfully', $installScriptPath));
    }

    public function createConfigProvider(string $namespace, string $path): void
    {
        $installScript = $this->buildStub('ConfigProvider', compact('namespace'));
        $installScriptPath = $path . '/src/ConfigProvider.php';
        file_put_contents($installScriptPath, $installScript);
        $this->output->success(\sprintf('%s Created Successfully', $installScriptPath));
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Plugin Path')
            ->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'Plug-in Name')
            ->addOption('type', 'type', InputOption::VALUE_OPTIONAL, 'Plugin type, default mix optional mix,frond,backend')
            ->addOption('description', 'desc', InputOption::VALUE_OPTIONAL, 'Plug-in Introduction')
            ->addOption('author', 'author', InputOption::VALUE_OPTIONAL, 'Plugin Author Information');
    }

    private function createViewScript(string $namespace, string $path): void
    {
        ! is_dir($path . '/web') && mkdir($path . '/web', 0o775);
    }
}
