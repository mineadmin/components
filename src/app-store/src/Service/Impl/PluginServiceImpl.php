<?php

namespace Xmo\AppStore\Service\Impl;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Database\Seeders\Seed;
use Hyperf\Support\Composer;
use Hyperf\Validation\ValidatorFactory;
use Nette\Utils\FileSystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Xmo\AppStore\Plugin;
use Xmo\AppStore\Service\PluginService;
use Xmo\AppStore\Utils\FileSystemUtils;

class PluginServiceImpl implements PluginService
{

    /**
     * Local plugins already detected
     * @var array
     */
    public static array $localPlugins = [];

    /**
     * Required item testing of mine.json file
     * Using hyperf/validator
     * @var array|string[]
     */
    private static array $mineJsonRequiredKeys = [
        'name'  =>  'required|string',
        'description'   =>  'required|string',
        'author'    =>  'required|string',
        'composer'   =>  'required|array',
        'package'   =>  'array',
        'installScript' =>  'required|string',
        'uninstallScript'   =>  'required|string'
    ];

    private readonly array $config;


    public function __construct(
        private readonly ValidatorFactory $validatorFactory,
        ConfigInterface $config,
        private readonly Migrator $migrator,
        private readonly Seed $seed
    ){
        $this->config = $config->get('mine-extension',[]);
    }

    /**
     * @param string $path
     * @return void
     */
    public function register(string $path): void
    {
        $info = $this->read($path);
        if (!$info['status']){
            self::$localPlugins[] = $info;
            return;
        }
        $loader = Composer::getLoader();

        // If it is already installed
        $composer = $info['composer'];
        if (!empty($composer['psr-4'])){
            // Register psr4
            foreach ($composer['psr-4'] as $namespace => $src){
                $loader->addPsr4($namespace,$path.'/'.$src);
            }
        }

        if (!empty($composer['class_map'])){
            // Register class_map
            $loader->addClassMap($composer['class_map']);
        }
    }

    /**
     * Reads the Mine plugin information through the given directory.
     * And check the legitimacy of the plugin
     * @param string $path
     * @return array
     */
    public function read(string $path): array
    {
        return Plugin::read($path);
    }


    /**
     * Installation of local plug-ins.
     */
    public function installExtension(string $path): void
    {
        $info = $this->read($path);
        if ($info['status']) {
            throw new \RuntimeException(sprintf('The given directory %s is the directory where the plugin has already been installed.', $path));
        }
        $installScript = $info['installScript'];
        $installScriptInstance = ApplicationContext::getContainer()->make($installScript);

        /*
         * The seeder and databases of the directory where the plugin is located are the data migration directories.
         * The . /web directory directly into the specified front-end source code directory.
         * and execute the plugin's installation script afterwards
         */
        $this->migrator->run([$path . '/Database/Migrations']);
        $this->seed->run([$path . '/Database/Seeders']);
        if (method_exists($installScriptInstance, '__invoke')) {
            $installScriptInstance();
        }
        /*
         * If the plugin has a front-end file, copy it.
         */
        if (file_exists($path . '/web')) {
            $front_directory = $this->config['front_directory'];
            if (! file_exists($front_directory)) {
                throw new \RuntimeException('The front-end source code directory does not exist or does not have permission to write to it');
            }
            $finder = Finder::create()
                ->files()
                ->in($path.'/web');
            foreach ($finder as $file){
                var_dump($file);
                /**
                 * @var SplFileInfo $file
                 */
                $filepath = $file->getPath();
            }
            // todo 整个web 目录移植。目前这个方法不行。因为卸载的时候恢复不了源文件
            FileSystemUtils::copyDirectory($path . '/web', $front_directory);
        }

        file_put_contents($path . '/install.lock', 1);
    }

    /**
     * Get all locally extensions.
     * @throws \JsonException
     */
    public function getLocalExtensions(): array
    {
        $extensionPath = $this->getLocalExtensionsPath();
        if (! is_dir($extensionPath)) {
            return [];
        }
        $finder = Finder::create()
            ->files()
            ->name('mine.json')
            ->in($extensionPath);
        if (! $finder->hasResults()) {
            return [];
        }
        $result = [];
        foreach ($finder as $file) {
            $path = $file->getPath();
            /**
             * @var SplFileInfo $file
             */
            $info = $this->read($path);
            $result[] = $info;
        }
        return $result;
    }

    /**
     * Uninstall locally installed plug-ins.
     */
    public function uninstallExtension(string $path): void
    {
        $info = $this->read($path);
        if (! file_exists($path . '/install.lock')) {
            throw new \RuntimeException(sprintf('Plugin %s not installed, cannot be uninstalled', $path));
        }
        $uninstallScript = $info['uninstallScript'];
        $uninstallScriptInstance = ApplicationContext::getContainer()->make($uninstallScript);
        $this->migrator->rollback([$path . '/Database/Migrations']);

        if (method_exists($uninstallScriptInstance, '__invoke')) {
            $uninstallScriptInstance();
        }
        if (file_exists($path . '/web')) {
            $front_directory = $this->config['front_directory'];
            if (! file_exists($front_directory)) {
                throw new \RuntimeException('The front-end source code directory does not exist or does not have permission to write to it');
            }
            $finder = Finder::create()
                ->files()
                ->in($path . '/web');
            foreach ($finder as $file) {
                /**
                 * @var \SplFileInfo $file
                 */
                $path = substr($file->getPath(), $path);
                var_dump($path);
            }
        }
        FileSystem::delete($path . '/install.lock');
    }


    /**
     * Get local plugin directory.
     */
    public function getLocalExtensionsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . $this->getExtensionDirectory();
    }

    private function getBasePath(): string
    {
        return BASE_PATH;
    }

    private function getExtensionDirectory(): string
    {
        return 'plugin';
    }
}