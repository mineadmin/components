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

namespace Mine\Generator\Command;

use Hyperf\Command\Annotation\Command;
use Mine\Annotation\Service;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\TraitType;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GeneratorServiceCommand extends AbstractGeneratorCommand
{
    protected ?string $name = 'gen:mine-service';

    public function __invoke(): void
    {
        $name = $this->input->getArgument('name');
        if (empty($name)) {
            throw new \RuntimeException('name argument not found');
        }
        $impl = $this->input->getOption('impl');
        if (empty($impl)) {
            $impl = $this->getConfig('service.impl');
        }

        $path = $this->input->getOption('path') ?: $this->getDefaultPath();
        $implPath = $path . DIRECTORY_SEPARATOR . $impl;
        if (! is_dir($path) && (! mkdir($path, 0777, true) && ! is_dir($path))) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
        if (! is_dir($implPath) && (! mkdir($implPath, 0777, true) && ! is_dir($implPath))) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $implPath));
        }
        $this->output->success(sprintf('Directory "%s" created', $path));
        $this->output->success(sprintf('Directory "%s" created', $implPath));

        $path = realpath($path);

        $implName = $name . $impl;

        $force = (bool) ($this->input->getOption('force') ?? false);
        $serviceFilepath = $path . DIRECTORY_SEPARATOR . $name . '.php';
        $implFilePath = $path . DIRECTORY_SEPARATOR . $impl . DIRECTORY_SEPARATOR . $name . $impl . '.php';
        if (file_exists($serviceFilepath) || file_exists($implFilePath)) {
            if ($force !== true) {
                throw new \RuntimeException(sprintf('file %s exists', $serviceFilepath));
            }
            $servicePhpFile = PhpFile::fromCode(file_get_contents($serviceFilepath));
            $implPhpFile = PhpFile::fromCode(file_get_contents($implFilePath));
            $this->handleServiceIo($servicePhpFile, $serviceFilepath);
            $this->handleImplIo($implPhpFile, $implFilePath);
            return;
        }
        $serviceNamespace = $this->input->getOption('namespace');
        if (empty($serviceNamespace)) {
            $serviceNamespace = $this->getDefaultNamespace();
        }
        $serviceNamespace = rtrim($serviceNamespace, '\\');
        $implNamespace = $serviceNamespace . '\\' . $impl;
        $serviceClass = $serviceNamespace . '\\' . $name;
        $servicePhpFile = new PhpFile();
        $servicePhpFile->addNamespace($serviceNamespace)
            ->addInterface($name);
        $implPhpFile = new PhpFile();
        $implPhpFile->addNamespace($implNamespace)
            ->addUse($serviceClass, 'BaseContract')
            ->addClass($implName)
            ->addImplement($serviceClass);
        $this->handleServiceIo($servicePhpFile, $serviceFilepath);
        $this->handleImplIo($implPhpFile, $implFilePath);
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('mapper', 'mp', InputOption::VALUE_REQUIRED, 'mapper class');
        $this->addOption('impl', 'ip', InputOption::VALUE_OPTIONAL, 'impl path');
    }

    protected function getDefaultPath(): string
    {
        return $this->getConfig('service.path');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig('service.namespace');
    }

    protected function handleClass(ClassType|EnumType|InterfaceType|TraitType $class, PhpFile $phpFile, PhpNamespace $namespace)
    {
        if (! $class instanceof ClassType) {
            return;
        }
        $mapper = $this->input->getOption('mapper');
        if (empty($mapper) || ! class_exists($mapper)) {
            throw new \RuntimeException(sprintf('the Mapper %s Notfound', $mapper));
        }
        if (! $this->hasUse($class, $mapper)) {
            $class->getNamespace()?->addUse($mapper, 'Mapper');
        }
        $methods = $class->getMethods();
        if (empty($methods['__construct'])) {
            $class->addMethod('__construct')
                ->addPromotedParameter('mapper')
                ->setType($mapper)
                ->setPrivate()->setReadOnly();
        }
        if (empty($methods['getMapper'])) {
            $class->addMethod('getMapper')
                ->setReturnType($mapper)
                ->setPublic()
                ->setBody(
                    <<<'PHPSCRIPT'
return $this->mapper;
PHPSCRIPT
                );
        }

        if (! $this->hasUse($class, Service::class)) {
            $class->getNamespace()?->addUse(Service::class);
        }
        foreach ($class->getAttributes() as $attribute) {
            if ($attribute->getName() === Service::class) {
                return;
            }
        }
        $class->addAttribute(Service::class);
    }

    protected function handleServiceIo(PhpFile $php, string $serviceFile): void
    {
        if ($php->hasStrictTypes()) {
            $php->setStrictTypes();
        }
        foreach ($php->getNamespaces() as $namespace) {
            if (method_exists($this, 'defaultServiceUse')) {
                $uses = $this->defaultUse();
                $nowUses = $namespace->getUses();
                foreach ($uses as $useValue) {
                    $use = $useValue[0];
                    $as = $useValue[1] ?? null;
                    if (empty($nowUses[$use])) {
                        $namespace->addUse($use, $as);
                    }
                }
            }
            foreach ($namespace->getClasses() as $className => $classAst) {
                $this->handleClass($classAst, $php, $namespace);
                break;
            }
        }
        file_put_contents($serviceFile, (string) $php);
    }

    protected function handleImplIo(PhpFile $php, string $implFile): void
    {
        if ($php->hasStrictTypes()) {
            $php->setStrictTypes();
        }
        foreach ($php->getNamespaces() as $namespace) {
            if (method_exists($this, 'defaultImplUse')) {
                $uses = $this->defaultUse();
                $nowUses = $namespace->getUses();
                foreach ($uses as $useValue) {
                    $use = $useValue[0];
                    $as = $useValue[1] ?? null;
                    if (empty($nowUses[$use])) {
                        $namespace->addUse($use, $as);
                    }
                }
            }
            foreach ($namespace->getClasses() as $className => $classAst) {
                $this->handleClass($classAst, $php, $namespace);
                break;
            }
        }
        file_put_contents($implFile, (string) $php);
    }

    protected function defaultServiceUse(): array
    {
        return [];
    }

    protected function defaultImplUse(): array
    {
        return [];
    }
}
