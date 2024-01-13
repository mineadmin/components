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

use Hyperf\Collection\Arr;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Builder;
use Mine\Abstracts\CrudMapper;
use Mine\Abstracts\Mapper;
use Mine\Annotation\MapperModel;
use Mine\Contract\DeleteMapperContract;
use Mine\Contract\PageMapperContract;
use Mine\Contract\SaveOrUpdateMapperContract;
use Mine\Contract\UpdateMapperContract;
use Mine\Traits\DeleteMapperTrait;
use Mine\Traits\SaveOrUpdateMapperTrait;
use Mine\Traits\SelectMapperTrait;
use Mine\Traits\UpdateMapperTrait;
use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\TraitType;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GeneratorMapperCommand extends AbstractGeneratorCommand
{
    protected ?string $name = 'gen:mine-mapper';

    protected function configure()
    {
        parent::configure();
        $this->addOption('model', 'mo', InputOption::VALUE_REQUIRED, 'model class');
        $this->addOption('mode', 'md', InputOption::VALUE_OPTIONAL, 'use mode,all,c,r,u,d');
    }

    protected function getDefaultPath(): string
    {
        return $this->getConfig('mapper.path');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig('mapper.namespace');
    }

    protected function defaultUse(): array
    {
        return [
            [
                MapperModel::class,
                'Model',
            ],
            [
                Mapper::class,
            ],
        ];
    }

    protected function handleClass(ClassType|EnumType|InterfaceType|TraitType $class, PhpFile $phpFile, PhpNamespace $namespace)
    {
        $model = $this->input->getOption('model');
        if (! class_exists($model)) {
            throw new \RuntimeException(sprintf('Model %s class not found', $model));
        }
        $mode = $this->input->getOption('mode') ?? 'crud';
        if ($mode === 'crud') {
            $this->handleCrud($class, $model);
        }

        if ($mode === 'r') {
            $this->handleRead($class, $model);
        }

        if ($mode === 'u') {
            $this->handleUpdate($class, $model);
        }

        if ($mode === 'c') {
            $this->handleCreate($class, $model);
        }

        if ($mode === 'd') {
            $this->handleDelete($class, $model);
        }
    }

    protected function checkExtend(ClassType $class, array|string $extend): bool
    {
        $extends = $class->getExtends();
        if (empty($extends)) {
            return false;
        }
        if (is_string($extend)) {
            return $extends === $extend;
        }
        return true;
    }

    protected function handleCrud(ClassType $class, string $model): void
    {
        if ($this->checkExtend($class, Mapper::class)) {
            throw new \RuntimeException(sprintf('The class has already inherited AbstractMapper'));
        }
        $this->buildComment($class);
        $this->addUse($class, CrudMapper::class, 'Base');
        if ($class->getExtends() && $class->getExtends() !== CrudMapper::class) {
            throw new \RuntimeException('the Mapper not extend fail');
        }
        if ($class->getExtends() === null) {
            $class->setExtends(CrudMapper::class);
        }
        $this->addUse($class, $model, 'CrudModel');
        $attributes = $class->getAttributes();
        if (empty($attributes) || count(Arr::where($attributes, function (Attribute $attribute) {
            return $attribute->getName() === MapperModel::class;
        })) <= 0) {
            $class->addAttribute(MapperModel::class, ['model' => new Literal('CrudModel::class')]);
        }
        $methods = $class->getMethods();
        if (empty($methods['handleSearch'])) {
            $this->buildCrudHandleSearch($class);
        }
    }

    protected function addUse(ClassType $class, string $use, ?string $alias = null)
    {
        if (! $this->hasUse($class, $use)) {
            $class->getNamespace()?->addUse($use, $alias);
        }
    }

    protected function buildComment(ClassType $class)
    {
        if ($class->getComment() === null || ! str_contains($class->getComment(), 'Mapper<')) {
            $class->addComment('@implements Mapper<CrudModel>');
        }
    }

    protected function buildCrudHandleSearch(ClassType $class)
    {
        if (! $this->hasUse($class, Builder::class)) {
            $class->getNamespace()?->addUse(Builder::class);
        }
        $method = $class->addMethod('handleSearch');
        $method->setComment("@param Builder \$query builder\n@param array \$params 查询参数\n@return Builder\n");
        $method->setReturnType(Builder::class);
        $method->setProtected();
        $builder = $method->addParameter('query');
        $builder->setType(Builder::class);
        $params = $method->addParameter('params', []);
        $params->setType('array');
        $method->addBody(
            <<<'PHPSCRIPT'
return $query;
PHPSCRIPT
        );
    }

    protected function handleCreate(ClassType $class, string $model): void
    {
        if ($this->checkExtend($class, CrudMapper::class)) {
            throw new \RuntimeException('CurdMapper extend');
        }
        $this->addUse($class, SaveOrUpdateMapperContract::class, 'Create');
        $this->addUse($class, SaveOrUpdateMapperTrait::class, 'CreateTrait');
        $this->addUse($class, $model, 'CrudModel');
        $class->addTrait(SaveOrUpdateMapperTrait::class);
        $class->addComment(
            <<<'PHPSCRIPT'
@implements Create<CrudModel>
PHPSCRIPT
        );
        $this->buildComment($class);
    }

    protected function handleRead(ClassType $class, string $model): void
    {
        if ($this->checkExtend($class, CrudMapper::class)) {
            throw new \RuntimeException('CurdMapper extend');
        }
        $this->addUse($class, $model, 'CrudModel');
        $this->addUse($class, PageMapperContract::class, 'Select');
        $this->addUse($class, SelectMapperTrait::class, 'SelectTrait');
        $class->addTrait(SelectMapperTrait::class);
        $class->addComment('@implements Select<CrudModel>');
    }

    protected function handleUpdate(ClassType $class, string $model): void
    {
        if ($this->checkExtend($class, CrudMapper::class)) {
            throw new \RuntimeException('CurdMapper extend');
        }
        $this->addUse($class, $model, 'CrudModel');
        $this->addUse($class, UpdateMapperContract::class, 'Update');
        $this->addUse($class, UpdateMapperTrait::class, 'UpdateTrait');
        $class->addTrait(UpdateMapperTrait::class);
        $class->addComment('@implements Update<CrudModel>');
    }

    protected function handleDelete(ClassType $class, string $model): void
    {
        if ($this->checkExtend($class, CrudMapper::class)) {
            throw new \RuntimeException('CurdMapper extend');
        }
        $this->addUse($class, $model, 'CrudModel');
        $this->addUse($class, DeleteMapperContract::class, 'Delete');
        $this->addUse($class, DeleteMapperTrait::class, 'DeleteTrait');
        $class->addTrait(DeleteMapperTrait::class);
    }
}
