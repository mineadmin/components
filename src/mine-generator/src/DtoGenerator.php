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

namespace Mine\Generator;

use Hyperf\Collection\Collection;
use Hyperf\Support\Filesystem\Filesystem;
use Mine\Exception\NormalStatusException;
use Mine\Generator\Contracts\GeneratorTablesContract;
use Mine\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

class DtoGenerator extends MineGenerator implements CodeGenerator
{
    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    protected Collection $columns;

    /**
     * 设置生成信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $tablesContract): DtoGenerator
    {
        $this->tablesContract = $tablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($tablesContract->getModuleName()) || empty($tablesContract->getMenuName())) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->setNamespace($this->tablesContract->getNamespace());

        $this->columns = $tablesContract->getColumns();

        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        $module = Str::title($this->tablesContract->getModuleName()[0]) . mb_substr($this->tablesContract->getModuleName(), 1);
        if ($this->tablesContract->getGenerateType()->value === 1) {
            $path = BASE_PATH . "/runtime/generate/php/app/{$module}/Dto/";
        } else {
            $path = BASE_PATH . "/app/{$module}/Dto/";
        }
        $this->filesystem->exists($path) || $this->filesystem->makeDirectory($path, 0755, true, true);
        $this->filesystem->put($path . "{$this->getClassName()}.php", $this->replace()->getCodeContent());
    }

    public function preview(): string
    {
        return $this->replace()->getCodeContent();
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
        return $this->getStubDir() . '/dto.stub';
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
    protected function placeholderReplace(): DtoGenerator
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
            '{COMMENT}',
            '{CLASS_NAME}',
            '{LIST}',
        ];
    }

    /**
     * 获取要替换占位符的内容.
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->initNamespace(),
            $this->getComment(),
            $this->getClassName(),
            $this->getList(),
        ];
    }

    /**
     * 初始化命名空间.
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . '\Dto';
    }

    /**
     * 获取控制器注释.
     */
    protected function getComment(): string
    {
        return $this->tablesContract->getMenuName() . 'Dto （导入导出）';
    }

    /**
     * 获取类名称.
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName() . 'Dto';
    }

    protected function getList(): string
    {
        $phpCode = '';
        foreach ($this->columns as $index => $column) {
            $phpCode .= str_replace(
                ['NAME', 'INDEX', 'FIELD'],
                [$column['column_comment'] ?: $column['column_name'], $index, $column['column_name']],
                $this->getCodeTemplate()
            );
        }
        return $phpCode;
    }

    protected function getCodeTemplate(): string
    {
        return sprintf(
            "    %s\n    %s\n\n",
            '#[ExcelProperty(value: "NAME", index: INDEX)]',
            'public string $FIELD;'
        );
    }
}
