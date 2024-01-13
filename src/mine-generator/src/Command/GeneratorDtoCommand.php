<?php

namespace Mine\Generator\Command;

use Hyperf\ApiDocs\Annotation\ApiModel;
use Hyperf\ApiDocs\Annotation\ApiModelProperty;
use Hyperf\Collection\Arr;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Builder as SchemaBuilder;
use Hyperf\Database\Schema\Column;
use Hyperf\Stringable\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\TraitType;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GeneratorDtoCommand extends AbstractGeneratorCommand
{
    protected ?string $name = 'gen:mine-dto';

    protected string $description = 'generator Dto';
    public function configure()
    {
        $this->addOption('model','mo',InputOption::VALUE_OPTIONAL,'Generate DTO classes based on the model');
        $this->addOption('property','p',InputOption::VALUE_OPTIONAL,'Generate DTO classes based on parameters');
        $this->addOption('table-hidden','m-h',InputOption::VALUE_OPTIONAL,'Do not generate the following fields');
        $this->addOption('property-case','property-case',InputOption::VALUE_OPTIONAL,'Field type 0 Snake 1 Hump');
        parent::configure();
    }

    protected function getDefaultPath(): string
    {
        return $this->getConfig('dto.path');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig('dto.namespace');
    }

    protected function defaultUse(): array
    {
        return [
            [
                ApiModel::class,'ApiDto'
            ],
            [
                ApiModelProperty::class,'ApiProperty'
            ]
        ];
    }

    protected function handlePropery(ClassType $class,array $property)
    {
        foreach ($property as $option){
            if ($class->hasProperty($option['name'])){
//                $this->output->warning(sprintf('property %s is already exists,now skip build',$option['name']));
                continue;
            }
            $io = $class->addProperty($option['name'],$option['value']);
            $io->setType('?'. $this->convertType($option['type']));
            foreach ($option['attribute'] as $attribute => $args){
                $io->addAttribute($attribute,$args);
            }
        }
    }

    protected function handleClass(
        TraitType|InterfaceType|ClassType|EnumType $class,
        PhpFile $phpFile,
        PhpNamespace $namespace,
    )
    {
        if (!($class instanceof ClassType)){
            throw new \RuntimeException('dto Errors');
        }
        $isApiModel = false;
        $attributes = $class->getAttributes();
        foreach ($attributes as $attr){
            if ($attr->getName() === ApiModel::class){
                $isApiModel = true;
            }
        }

        if (!$isApiModel){
            $class->addAttribute(ApiModel::class);
        }

        $model = $this->input->getOption('model');
        $propertyOption = $this->input->getOption('property');
        $builderProperty = [];
        $hidden = $this->input->getOption('table-hidden') ?? [];
        $propertyCase = (int)($this->input->getOption('property-case') ?? 0);
        if (!empty($model)){
            /**
             * @var Model $model
             * @var SchemaBuilder $builder
             */
            $builder = $model::getModel()->getConnection()->getSchemaBuilder();
            $columns = $builder->getColumns();
            $table = $model::getModel()->getTable();
            $tableColumns = $builder->getColumnListing($table);
            $keyName = $model::getModel()->getKeyName();
            $columns = Arr::where($columns,function (Column $column)use ($tableColumns,$keyName){
                return in_array($column->getName(),$tableColumns,true) && $keyName !== $column->getName();
            });
            $casts = $model::getModel()->getCasts();
            /**
             * @var Column[] $columns
             */
            foreach ($columns as $colum){
                if (in_array($colum->getName(),$hidden,true)){
                    continue;
                }
                $propertyName = $this->covertCase($colum->getName(),$propertyCase);
                $builderProperty[] = [
                    'name'  =>  $propertyName,
                    'value' =>  null,
                    'type'  =>  ($casts[$colum->getName()] ?? 'string'),
                    'attribute' =>  [
                        ApiModelProperty::class=>[
                            'value' =>  $colum->getComment(),
                            'example'   =>  $colum->getDefault(),
                            'required'  =>  true,
                        ]
                    ]
                ];
            }
        }
        if (!empty($propertyOption)){
            $propertys = explode('|',$propertyOption);
            foreach ($propertys as $property){
                $property = explode(',',$property);
                $propertyName = $this->covertCase($property[0] ?? '',$propertyCase);
                if (in_array($colum->getName(),$hidden,true)){
                    continue;
                }
                $builderProperty[] = [
                    'name'  =>  $propertyName,
                    'value' =>  $property[2] ?? null,
                    'type'  =>  ($property[3]??'string'),
                    'attribute' =>  [
                        ApiModelProperty::class=>[
                            'value' =>  $property[1] ?? null,
                            'example'   =>  $property[2] ?? null,
                            'required'  =>  true,
                        ]
                    ]
                ];
            }
        }
        if (count($builderProperty) > 0){
            $this->handlePropery($class,$builderProperty);
        }
    }

    protected function covertCase(string $property,int $case)
    {
        return $case === 0 ? Str::snake($property) : Str::camel($property);
    }

    protected function convertType(string $type): string
    {
        return $this->getConfig('dto.type.mapping.'.$type,$type);
    }

}