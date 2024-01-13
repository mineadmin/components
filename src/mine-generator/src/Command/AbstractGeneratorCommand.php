<?php

namespace Mine\Generator\Command;

use Hyperf\Command\Command;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\TraitType;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractGeneratorCommand extends Command
{

    protected function configure()
    {
        $this->addArgument('name',InputArgument::REQUIRED,'class name');
        $this->addOption('path','path',InputOption::VALUE_NONE,'generator file path');
        $this->addOption('force','f',InputOption::VALUE_NONE,'force put file');
        $this->addOption('namespace','namespace',InputOption::VALUE_NONE,'class in namespace');
    }

    protected function getConfig(string $key,mixed $default = null): mixed
    {
        return config('generator.'.$key,$default);
    }


    abstract protected function getDefaultPath(): string;

    abstract protected function getDefaultNamespace(): string;


    public function __invoke(): void
    {
        $path = $this->input->getOption('path') ?: $this->getDefaultPath();
        if (!file_exists($path)){
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
            $this->output->success(sprintf('Directory "%s" created',$path));
        }
        $path = realpath($path);

        $name = $this->input->getArgument('name');
        if (empty($name)){
            throw new \RuntimeException('name argument not found');
        }
        $force = (boolean)($this->input->getOption('force')??false);
        $filepath = $path.DIRECTORY_SEPARATOR.$name.'.php';
        if (file_exists($filepath)){
            if ($force !== true) {
                throw new \RuntimeException(sprintf('file %s exists', $filepath));
            }
            $this->handleIo(PhpFile::fromCode(file_get_contents($filepath)),$filepath);
            return;
        }
        $namespace = $this->input->getOption('namespace');
        if (empty($namespace)){
            $namespace = $this->getDefaultNamespace();
            $namespace = rtrim($namespace,'\\');
        }
        $file = new PhpFile();
        $file->addNamespace($namespace)
            ->addClass($name);
        $this->handleIo($file,$filepath);
        return;
    }

    protected function defaultUse(): array
    {
        return [];
    }


    protected function hasUse(ClassType $type,string $use): bool
    {
        $uses = $type->getNamespace()?->getUses() ?? [];
        foreach ($uses as $val){
            if ($val === $use){
                return true;
            }
        }
        return false;
    }

    protected function handleIo(PhpFile $php,string $filepath): void
    {
        if ($php->hasStrictTypes()){
            $php->setStrictTypes();
        }
        foreach ($php->getNamespaces() as $namespace){
            if (method_exists($this,'defaultUse')){
                $uses = $this->defaultUse();
                $nowUses = $namespace->getUses();
                foreach ($uses as $useValue){
                    $use = $useValue[0];
                    $as = $useValue[1] ?? null;
                    if (empty($nowUses[$use])){
                        $namespace->addUse($use,$as);
                    }
                }
            }
            foreach ($namespace->getClasses() as $className => $classAst){
                $this->handleClass($classAst,$php,$namespace);
                break;
            }
        }
        file_put_contents($filepath,(string)$php);
    }

    /**
     * 处理类
     * @param ClassType|InterfaceType|TraitType|EnumType $class
     * @param PhpFile $phpFile
     * @param PhpNamespace $namespace
     * @return mixed
     */
    abstract protected function handleClass(
        ClassType|InterfaceType|TraitType|EnumType $class,
        PhpFile $phpFile,
        PhpNamespace $namespace,
    );
}