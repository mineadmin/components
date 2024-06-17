<?php

/** @noinspection PhpIllegalStringOffsetInspection */
/* @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/*
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/xmo/MineAdmin
 */

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\Generator;

use Hyperf\Support\Filesystem\Filesystem;
use Mine\Exception\NormalStatusException;
use Mine\Generator\Contracts\GeneratorTablesContract;
use Mine\Generator\Traits\MapperGeneratorTraits;
use Mine\Helper\Str;
use Mine\Interfaces\ServiceInterface\GenerateColumnServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * Mapper类生成
 * Class MapperGenerator.
 */
class MapperGenerator extends MineGenerator implements CodeGenerator
{
    use MapperGeneratorTraits;

    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $tablesContract): MapperGenerator
    {
        $this->tablesContract = $tablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($tablesContract->getModuleName()) || empty($tablesContract->getMenuName())) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->setNamespace($this->tablesContract->getNamespace());
        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        $module = Str::title(
            $this->tablesContract->getModuleName()[0]
        ) .
            mb_substr($this->tablesContract->getModuleName(), 1);
        if ($this->tablesContract->getGenerateType()->value === 1) {
            $path = BASE_PATH . "/runtime/generate/php/app/{$module}/Mapper/";
        } else {
            $path = BASE_PATH . "/app/{$module}/Mapper/";
        }
        $this->filesystem->exists($path) || $this->filesystem->makeDirectory($path, 0755, true, true);
        $this->filesystem->put($path . "{$this->getClassName()}.php", $this->replace()->getCodeContent());
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->replace()->getCodeContent();
    }

    /**
     * 获取生成的类型.
     */
    public function getType(): string
    {
        return ucfirst($this->tablesContract->getType()->value);
    }

    /**
     * 获取业务名称.
     */
    public function getBusinessName(): string
    {
        return Str::studly(str_replace(env('DB_PREFIX', ''), '', $this->tablesContract->getTableName()));
    }

    /**
     * 设置代码内容.
     */
    public function setCodeContent(string $content)
    {
        $this->codeContent = $content;
    }

    /**
     * 获取代码内容.
     */
    public function getCodeContent(): string
    {
        return $this->codeContent;
    }

    /**
     * 获取模板地址
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . $this->getType() . '/mapper.stub';
    }

    /**
     * 读取模板内容.
     */
    protected function readTemplate(): string
    {
        return $this->filesystem->sharedGet($this->getTemplatePath());
    }

    /**
     * 占位符替换.
     */
    protected function placeholderReplace(): MapperGenerator
    {
        $this->setCodeContent(str_replace(
            $this->getPlaceHolderContent(),
            $this->getReplaceContent(),
            $this->readTemplate()
        ));

        return $this;
    }

    /**
     * 获取要替换的占位符.
     */
    protected function getPlaceHolderContent(): array
    {
        return [
            '{NAMESPACE}',
            '{USE}',
            '{COMMENT}',
            '{CLASS_NAME}',
            '{MODEL}',
            '{FIELD_ID}',
            '{FIELD_PID}',
            '{FIELD_NAME}',
            '{SEARCH}',
        ];
    }

    /**
     * 获取要替换占位符的内容.
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->initNamespace(),
            $this->getUse(),
            $this->getComment(),
            $this->getClassName(),
            $this->getModelName(),
            $this->getFieldIdName(),
            $this->getFieldPidName(),
            $this->getFieldName(),
            $this->getSearch(),
        ];
    }

    /**
     * 初始化服务类命名空间.
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . '\Mapper';
    }

    /**
     * 获取控制器注释.
     */
    protected function getComment(): string
    {
        return $this->tablesContract->getMenuName() . 'Mapper类';
    }

    /**
     * 获取使用的类命名空间.
     */
    protected function getUse(): string
    {
        return <<<UseNamespace
use {$this->getNamespace()}\\Model\\{$this->getBusinessName()};
UseNamespace;
    }

    /**
     * 获取类名称.
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName() . 'Mapper';
    }

    /**
     * 获取Model类名称.
     */
    protected function getModelName(): string
    {
        return $this->getBusinessName();
    }

    /**
     * 获取树表ID.
     */
    protected function getFieldIdName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->tablesContract->getoptions()['tree_id'] ?? false) {
                return $this->tablesContract->getOptions()['tree_id'];
            }
            return 'id';
        }
        return '';
    }

    /**
     * 获取树表父ID.
     */
    protected function getFieldPidName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->tablesContract->getoptions()['tree_pid'] ?? false) {
                return $this->tablesContract->getoptions()['tree_pid'];
            }
            return 'parent_id';
        }
        return '';
    }

    /**
     * 获取树表显示名称.
     */
    protected function getFieldName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->tablesContract->getoptions()['tree_name'] ?? false) {
                return $this->tablesContract->getoptions()['tree_name'];
            }
            return 'name';
        }
        return '';
    }

    /**
     * 获取搜索内容.
     */
    protected function getSearch(): string
    {
        $model = make(GenerateColumnServiceInterface::class)->mapper->getModel();
        //        $columns = $model->newQuery()
        //            ->where('table_id', $this->tablesContract->id)
        //            ->where('is_query', self::YES)
        //            ->get(['column_name', 'column_comment', 'query_type'])->toArray();
        $columns = $this->tablesContract->getColumns();
        $phpContent = '';
        foreach ($columns as $column) {
            $phpContent .= $this->getSearchCode($column);
        }

        return $phpContent;
    }
}
