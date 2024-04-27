<?php

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */

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
use Mine\Generator\Enums\GenerateTypeEnum;
use Mine\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * 控制器生成
 * Class ControllerGenerator.
 */
class ControllerGenerator extends MineGenerator implements CodeGenerator
{
    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $tablesContract): ControllerGenerator
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
        $module = Str::title($this->tablesContract->getModuleName()[0]) . mb_substr($this->tablesContract->getModuleName(), 1);
        if ($this->tablesContract->getGenerateType() === GenerateTypeEnum::ZIP) {
            $path = BASE_PATH . "/runtime/generate/php/app/{$module}/Controller/";
        } else {
            $path = BASE_PATH . "/app/{$module}/Controller/";
        }
        if (! empty($this->tablesContract->getPackageName())) {
            $path .= Str::title($this->tablesContract->getPackageName()) . '/';
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
     * 获取生成控制器的类型.
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
     * 获取短业务名称.
     */
    public function getShortBusinessName(): string
    {
        return Str::camel(str_replace(
            Str::lower($this->tablesContract->getModuleName()),
            '',
            str_replace(env('DB_PREFIX', ''), '', $this->tablesContract->getTableName())
        ));
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
     * 获取控制器模板地址
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . 'Controller/main.stub';
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
    protected function placeholderReplace(): ControllerGenerator
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
            '{USE}',
            '{CLASS_NAME}',
            '{SERVICE}',
            '{CONTROLLER_ROUTE}',
            '{FUNCTIONS}',
            '{REQUEST}',
            '{INDEX_PERMISSION}',
            '{RECYCLE_PERMISSION}',
            '{SAVE_PERMISSION}',
            '{READ_PERMISSION}',
            '{UPDATE_PERMISSION}',
            '{DELETE_PERMISSION}',
            '{REAL_DELETE_PERMISSION}',
            '{RECOVERY_PERMISSION}',
            '{IMPORT_PERMISSION}',
            '{EXPORT_PERMISSION}',
            '{DTO_CLASS}',
            '{PK}',
            '{STATUS_VALUE}',
            '{STATUS_FIELD}',
            '{NUMBER_FIELD}',
            '{NUMBER_TYPE}',
            '{NUMBER_VALUE}',
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
            $this->getUse(),
            $this->getClassName(),
            $this->getServiceName(),
            $this->getControllerRoute(),
            $this->getFunctions(),
            $this->getRequestName(),
            sprintf('%s, %s', Str::lower($this->tablesContract->getModuleName()) . ':' . $this->getShortBusinessName(), $this->getMethodRoute('index')),
            $this->getMethodRoute('recycle'),
            $this->getMethodRoute('save'),
            $this->getMethodRoute('read'),
            $this->getMethodRoute('update'),
            $this->getMethodRoute('delete'),
            $this->getMethodRoute('realDelete'),
            $this->getMethodRoute('recovery'),
            $this->getMethodRoute('import'),
            $this->getMethodRoute('export'),
            $this->getDtoClass(),
            $this->getPk(),
            $this->getStatusValue(),
            $this->getStatusField(),
            $this->getNumberField(),
            $this->getNumberType(),
            $this->getNumberValue(),
        ];
    }

    /**
     * 初始化控制器命名空间.
     */
    protected function initNamespace(): string
    {
        $namespace = $this->getNamespace() . '\\Controller';
        if (! empty($this->tablesContract->getPackageName())) {
            return $namespace . '\\' . Str::title($this->tablesContract->getPackageName());
        }
        return $namespace;
    }

    /**
     * 获取控制器注释.
     */
    protected function getComment(): string
    {
        return $this->tablesContract->getMenuName() . '控制器';
    }

    /**
     * 获取使用的类命名空间.
     */
    protected function getUse(): string
    {
        return <<<UseNamespace
use {$this->getNamespace()}\\Service\\{$this->getBusinessName()}Service;
use {$this->getNamespace()}\\Request\\{$this->getBusinessName()}Request;
UseNamespace;
    }

    /**
     * 获取控制器类名称.
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName() . 'Controller';
    }

    /**
     * 获取服务类名称.
     */
    protected function getServiceName(): string
    {
        return $this->getBusinessName() . 'Service';
    }

    /**
     * 获取控制器路由.
     */
    protected function getControllerRoute(): string
    {
        return sprintf(
            '%s/%s',
            Str::lower($this->tablesContract->getModuleName()),
            $this->getShortBusinessName()
        );
    }

    protected function getFunctions(): string
    {
        $menus = $this->tablesContract->getGenerateMenus() ? explode(',', $this->tablesContract->getGenerateMenus()) : [];
        $otherMenu = [$this->tablesContract->getType()->value === 'single' ? 'singleList' : 'treeList'];
        if (in_array('recycle', $menus)) {
            $otherMenu[] = $this->tablesContract->getType()->value === 'single' ? 'singleRecycleList' : 'treeRecycleList';
            array_push($otherMenu, ...['realDelete', 'recovery']);
            unset($menus[array_search('recycle', $menus)]);
        }
        array_unshift($menus, ...$otherMenu);
        $phpCode = '';
        $path = $this->getStubDir() . 'Controller/';
        foreach ($menus as $menu) {
            $content = $this->filesystem->sharedGet($path . $menu . '.stub');
            $phpCode .= $content;
        }
        return $phpCode;
    }

    /**
     * 获取方法路由.
     */
    protected function getMethodRoute(string $route): string
    {
        return sprintf(
            '%s:%s:%s',
            Str::lower($this->tablesContract->getModuleName()),
            $this->getShortBusinessName(),
            $route
        );
    }

    protected function getDtoClass(): string
    {
        return sprintf(
            '\\%s\\Dto\\%s::class',
            $this->tablesContract->getNamespace(),
            $this->getBusinessName() . 'Dto'
        );
    }

    protected function getPk(): string
    {
        return $this->tablesContract->getPkName();
    }

    protected function getStatusValue(): string
    {
        return 'statusValue';
    }

    protected function getStatusField(): string
    {
        return 'statusName';
    }

    protected function getNumberField(): string
    {
        return 'numberName';
    }

    protected function getNumberType(): string
    {
        return 'numberType';
    }

    protected function getNumberValue(): string
    {
        return 'numberValue';
    }

    /**
     * 获取验证器.
     */
    protected function getRequestName(): string
    {
        return $this->getBusinessName() . 'Request';
    }
}
