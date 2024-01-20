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

namespace Mine\Command;

use Hyperf\Command\Command;
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
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\class_basename;

#[\Hyperf\Command\Annotation\Command]
class MineGenMapperCommand extends Command
{
    public function __invoke(): void
    {
        $model = $this->input->getOption('model');
        if (empty($model)) {
            $this->error('no model');
            return;
        }
        if (! str_contains($model, 'App')) {
            $model = 'App\\Model\\' . $model;
        }
        if (! class_exists($model)) {
            $this->output->error('model not found');
            return;
        }
        if (! $this->input->getOption('name')) {
            $name = class_basename($model) . 'Mapper';
        } else {
            $name = $this->input->getOption('name');
        }
        if (! $this->hasOption('path')) {
            $path = BASE_PATH . '/app/Mapper';
            $namespace = 'App\\Mapper';
            is_dir($path) || mkdir($path, 0777, true) || is_dir($path);
        } else {
            $path = $this->input->getOption('path');
            $namespace = 'App\\Mapper\\' . str_replace('/', '\\', $path);
            $path = BASE_PATH . DIRECTORY_SEPARATOR . $path;
        }
        $filepath = $path . DIRECTORY_SEPARATOR . $name . '.php';
        if (! $this->input->getOption('mode')) {
            $mode = ['all'];
        } else {
            $mode = explode(',', $this->input->getOption('mode'));
        }

        $factory = new BuilderFactory();
        if (file_exists($filepath)) {
            $this->error('now not reactor');
            return;
        }
        $node = $this->build($factory, $mode, $model, $namespace, $name);
        $prettyPrinter = new Standard();
        $fileBody = $prettyPrinter->prettyPrintFile($node);
        var_dump($fileBody);
        file_put_contents($filepath, $fileBody);
    }

    public function build(BuilderFactory $factory, array $mode, string $model, string $namespace, $mapperName): array
    {
        $node = $factory->namespace($namespace)
            ->addStmts([
                $factory->use($model)->as('Model'),
                $factory->use(Mapper::class),
                $factory->use(MapperModel::class),
            ]);
        $class = $factory->class($mapperName)
            ->setDocComment(
                <<<'DOCUMENT'
/**
 * @implements Mapper<Model>
 */
DOCUMENT
            );
        $class->addAttribute(new AttributeGroup([
            new Attribute(new Name('MapperModel'), [new Arg(new ClassConstFetch(new Name('Model'), 'class'))]),
        ]));
        if (in_array('all', $mode, true)) {
            $class->extend(new Name('Crud'));
            $class->addStmt(
                $factory
                    ->method('handleSearch')
                    ->makeProtected()
                    ->addParams([
                        $factory->param('params')->setType('array')->getNode(),
                        $factory->param('query')
                            ->setType(new Name\FullyQualified(Builder::class))
                            ->getNode(),
                    ])
                    ->addStmt(
                        new Stmt\Return_(new Variable('query'))
                    )
                    ->setReturnType(new Name\FullyQualified(Builder::class))
            );
            $node->addStmts([
                $factory->use(CrudMapper::class)->as('Crud'),
                $class,
            ]);
        } else {
            $node->addStmts([
                $factory->use(Mapper::class),
            ]);
            foreach ($mode as $m) {
                if ($m === 'select') {
                    $node->addStmt($factory->use(PageMapperContract::class)->as('SelectContract'));
                    $node->addStmt($factory->use(SelectMapperTrait::class)->as('SelectTrait'));
                    $class->implement('SelectContract');
                    $class->addStmt($factory->useTrait('SelectTrait'));
                }
                if ($m === 'save') {
                    $node->addStmt($factory->use(SaveOrUpdateMapperContract::class)->as('SaveContract'));
                    $node->addStmt($factory->use(SaveOrUpdateMapperTrait::class)->as('SaveTrait'));
                    $class->implement('SaveContract');
                    $class->addStmt($factory->useTrait('SaveTrait'));
                }
                if ($m === 'update') {
                    $node->addStmt($factory->use(UpdateMapperContract::class)->as('UpdateContract'));
                    $node->addStmt($factory->use(UpdateMapperTrait::class)->as('UpdateTrait'));
                    $class->implement('UpdateContract');
                    $class->addStmt($factory->useTrait('UpdateTrait'));
                }
                if ($m === 'delete') {
                    $node->addStmt($factory->use(DeleteMapperContract::class)->as('DeleteContract'));
                    $node->addStmt($factory->use(DeleteMapperTrait::class)->as('DeleteTrait'));
                    $class->implement('DeleteContract');
                    $class->addStmt($factory->useTrait('DeleteTrait'));
                }
            }
            $node->addStmt($class);
        }
        return $node->getNode()->stmts;
    }

    protected function configure()
    {
        $this->setDescription('快速生成 mapper');
        $this->setName('mine:gen-mapper');
        $this->addOption('model', 'model', InputOption::VALUE_REQUIRED, 'model class');
        $this->addOption('name', 'name', InputOption::VALUE_NONE, 'build file name');
        $this->addOption('path', 'path', InputOption::VALUE_NONE, 'build path');
        $this->addOption('mode', 'mode', InputOption::VALUE_NONE, 'crud,get,set,up,del default = crud');
    }
}
