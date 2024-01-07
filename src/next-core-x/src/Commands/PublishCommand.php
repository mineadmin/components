<?php

namespace Mine\NextCoreX\Commands;

use Composer\InstalledVersions;
use Hyperf\Command\Command;
use Symfony\Component\VarExporter\VarExporter;
use function Hyperf\Config\config;

class PublishCommand extends Command
{
    protected ?string $name = 'next-core-x:publish';

    protected string $description = 'publish next-core-x configure files';

    private array $dependencies = [

    ];

    private array $publish = [

    ];

    public function handle()
    {
        // 获取项目绝对目录
        $basePath = dirname(realpath(InstalledVersions::getInstallPath('next-core-x')),2);
        foreach ($this->publish as $source => $to){
            $toPath = $basePath.DIRECTORY_SEPARATOR.$to;
            copy($source,$toPath)
                ? $this->output->success(sprintf('copy [%s] to [%s] Successful',$source,$toPath))
                : $this->output->error(sprintf('copy [%s] to [%s] Fail',$source,$toPath));
        }
        $dependencies = array_merge(config('dependencies',[]),$this->dependencies);
        $dependenciesFile = $basePath.DIRECTORY_SEPARATOR.'config/autoload/dependencies.php';
        $dependenciesArr = VarExporter::export($dependencies);
        file_put_contents($dependenciesFile,"<?php return {$dependenciesArr};");
    }
}