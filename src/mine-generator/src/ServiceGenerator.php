<?php

/** @noinspection PhpIllegalStringOffsetInspection */
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
use Mine\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * 服务类生成
 * Class ServiceGenerator.
 */
class ServiceGenerator extends MineGenerator implements CodeGenerator
{
    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $tablesContract): ServiceGenerator
    {
        $this->tablesContract = $tablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($tablesContract->getModuleName()) || empty($tablesContract->menu_name)) {
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
        $module = Str::title($this->tablesContract->getModuleName()[0]) . mb_substr($this->tablesContract->getModuleName(), 1);
        if ($this->tablesContract->getGenerateType()->value === 1) {
            $path = BASE_PATH . "/runtime/generate/php/app/{$module}/Service/";
        } else {
            $path = BASE_PATH . "/app/{$module}/Service/";
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
        return $this->getStubDir() . $this->getType() . '/service.stub';
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
    protected function placeholderReplace(): ServiceGenerator
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
            '{MAPPER}',
            '{FIELD_ID}',
            '{FIELD_PID}',
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
            $this->getMapperName(),
            $this->getFieldIdName(),
            $this->getFieldPidName(),
        ];
    }

    /**
     * 初始化服务类命名空间.
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . '\Service';
    }

    /**
     * 获取控制器注释.
     */
    protected function getComment(): string
    {
        return $this->tablesContract->getMenuName() . '服务类';
    }

    /**
     * 获取使用的类命名空间.
     */
    protected function getUse(): string
    {
        return <<<UseNamespace
use {$this->getNamespace()}\\Mapper\\{$this->getBusinessName()}Mapper;
UseNamespace;
    }

    /**
     * 获取控制器类名称.
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName() . 'Service';
    }

    /**
     * 获取Mapper类名称.
     */
    protected function getMapperName(): string
    {
        return $this->getBusinessName() . 'Mapper';
    }

    /**
     * 获取树表ID.
     */
    protected function getFieldIdName(): string
    {
        if ($this->getType() == 'Tree') {
            if ($this->tablesContract->getOptions()['tree_id'] ?? false) {
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
            if ($this->tablesContract->getOptions()['tree_pid'] ?? false) {
                return $this->tablesContract->getOptions()['tree_pid'];
            }
            return 'parent_id';
        }
        return '';
    }
}
