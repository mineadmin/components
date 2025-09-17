<?php

namespace Mine\Core;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Di\Exception\Exception;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Mine\AppStore\Plugin;
use Symfony\Component\Console\Application;
use function Hyperf\Support\with;

final class App
{
    /**
     * Current version of the application.
     */
    public const VERSION = '3.1.0';

    /**
     * Indicates if the application is in debug mode.
     */
    private bool $debug = false;

    private ?string $proxyFileDirPath = null;

    private ?ScanHandlerInterface $handler = null;

    /**
     * Indicates if the scan cache is enabled.
     */
    private bool $scanCacheable = false;

    /**
     * Symfony Console Application instance.
     */
    private Application $application;

    /**
     * Hyperf Container instance.
     */
    private ContainerInterface $container;

    private int $startTime;

    private string $basePath;

    /**
     * Default PHP ini configurations.
     */
    private array $configurations  = [
        'display_errors' => 'on',
        'display_startup_errors' => 'on',
        'memory_limit' => '1G',
    ];

    private function __construct(){
        $this->startTime = microtime(true);
    }

    public static function configure(string $basePath): self
    {
        return with(new self(),function (self $app)use($basePath){
            $app->basePath = $basePath;
            return $app;
        });
    }

    /**
     * @return int
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function run()
    {
        class_exists(Plugin::class) && Plugin::init();
        class_exists(ClassLoader::class) && ClassLoader::init($this->proxyFileDirPath,$this->configPath(),$this->handler);

        $this->initContainer();
        $this->initConsoleApplication();
        $this->initializeIniConfigs();

        $this->application->run();
    }

    public function mergeConfigurations(array $iniValues): self
    {
        $this->configurations = array_merge($this->configurations, $iniValues);
        return $this;
    }

    public function proxyFileDirPath(string $proxyFileDirPath): self
    {
        $this->proxyFileDirPath = $proxyFileDirPath;
        return $this;
    }

    private function initConsoleApplication(): void
    {
       $this->application = $this->container->get(ApplicationInterface::class);
    }

    /**
     * @throws Exception
     */
    private function initContainer(): void
    {
        $this->container = new Container((new DefinitionSourceFactory())());
        $this->container->set(App::class,$this);
        ApplicationContext::setContainer($this->container);
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }


    public function configPath(): string
    {
        return $this->basePath() . '/config';
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function debug(bool $debug = true): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function scanCacheable(bool $scanCacheable = true): self
    {
        $this->scanCacheable = $scanCacheable;
        return $this;
    }

    public function isScanCacheable(): bool
    {
        return $this->scanCacheable;
    }

    private function initializeIniConfigs(): void
    {
        foreach ($this->configurations as $key => $value) {
            ini_set($key, $value);
        }
    }

}