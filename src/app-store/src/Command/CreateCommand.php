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

namespace Xmo\AppStore\Command;

use Hyperf\Command\Annotation\Command;
use Mine\Helper\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Xmo\AppStore\Enums\PluginTypeEnum;
use Xmo\AppStore\Plugin;

#[Command]
class CreateCommand extends AbstractCommand
{
    protected string $description = 'Creating Plug-ins';

    public function __invoke()
    {
        $path = $this->input->getArgument('path');
        $name = $this->input->getOption('name');
        $type = $this->input->getOption('type') ?? 'mix';
        $type = PluginTypeEnum::fromValue($type);
        if (empty($name)) {
            $this->output->error('Plugin name is empty');
            return;
        }
        if ($type === null) {
            $this->output->error('Plugin type is empty');
            return;
        }

        $pluginPath = Plugin::PLUGIN_PATH . '/' . $path;
        if (file_exists($pluginPath)) {
            $this->output->error(sprintf('Plugin directory %s already exists', $path));
            return;
        }
        $createDirectors = [
            $pluginPath, $pluginPath . '/src', $pluginPath . '/Database',
        ];
        foreach ($createDirectors as $directory) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        $this->createMineJson($pluginPath, $name, $type);
    }

    public function createMineJson(string $path, string $name, PluginTypeEnum $pluginType): void
    {
        $output = new \stdClass();
        $output->name = $name;
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
            $namespace = 'Plugin\\' . Str::studly($name);

            $this->createInstallScript($namespace, $path);
            $this->createUninstallScript($namespace, $path);
            $this->createConfigProvider($namespace, $path);
            $this->createViewScript($namespace, $path);
            $output->composer = [
                'require' => [],
                'psr-4' => [
                    $namespace . '\\' => 'src',
                ],
                'installScript' => $namespace . '\\InstallScript',
                'uninstallScript' => $namespace . '\\UninstallScript',
                'config' => $namespace . '\\ConfigProvider',
            ];
        }

        if ($pluginType === PluginTypeEnum::Mix || $pluginType === PluginTypeEnum::Frond) {
            $output->package = [
                'dependencies' => [
                ],
            ];
        }

        $output = json_encode($output, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE, 512);
        file_put_contents($path . '/mine.json', $output);
        $this->output->success(sprintf('%s 创建成功', $path . '/mine.json'));
    }

    public function createInstallScript(string $namespace, string $path): void
    {
        $installScript = $this->buildStub('InstallScript', compact('namespace'));
        $installScriptPath = $path . '/src/InstallScript.php';
        file_put_contents($installScriptPath, $installScript);
        $this->output->success(sprintf('%s Created Successfully', $installScriptPath));
    }

    public function buildStub(string $stub, array $replace): string
    {
        $stubPath = $this->getStubDirectory() . '/' . $stub . '.stub';
        if (! file_exists($stubPath)) {
            throw new \RuntimeException(sprintf('File %s does not exist', $stubPath));
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
        $this->output->success(sprintf('%s Created Successfully', $installScriptPath));
    }

    public function createConfigProvider(string $namespace, string $path): void
    {
        $installScript = $this->buildStub('ConfigProvider', compact('namespace'));
        $installScriptPath = $path . '/src/ConfigProvider.php';
        file_put_contents($installScriptPath, $installScript);
        $this->output->success(sprintf('%s Created Successfully', $installScriptPath));
    }

    public function commandName(): string
    {
        return 'create';
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Plugin Path');
        $this->addOption('name', 'name', InputOption::VALUE_REQUIRED, 'Plug-in Name');
        $this->addOption('type', 'type', InputOption::VALUE_OPTIONAL, 'Plugin type, default mix optional mix,frond,backend');
        $this->addOption('description', 'desc', InputOption::VALUE_OPTIONAL, 'Plug-in Introduction');
        $this->addOption('author', 'author', InputOption::VALUE_OPTIONAL, 'Plugin Author Information');
    }

    private function createViewScript(string $namespace, string $path): void
    {
        ! is_dir($path . '/web') && mkdir($path . '/web', 0775);
    }
}
