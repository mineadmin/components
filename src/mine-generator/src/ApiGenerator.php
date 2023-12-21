<?php

/** @noinspection PhpExpressionResultUnusedInspection */
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
use Mine\Helper\Str;
use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * JS API文件生成
 * Class ApiGenerator.
 */
class ApiGenerator extends MineGenerator implements CodeGenerator
{
    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $generatorTablesContract): ApiGenerator
    {
        $this->tablesContract = $generatorTablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($generatorTablesContract->getModuleName()) || empty($generatorTablesContract->getMenuName())) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        $filename = Str::camel(str_replace(env('DB_PREFIX'), '', $this->tablesContract->getTablename()));
        $module = Str::lower($this->tablesContract->getModuleName());
        $this->filesystem->makeDirectory(BASE_PATH . "/runtime/generate/vue/src/api/{$module}", 0755, true, true);
        $path = BASE_PATH . "/runtime/generate/vue/src/api/{$module}/{$filename}.js";
        $this->filesystem->put($path, $this->replace()->getCodeContent());
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->replace()->getCodeContent();
    }

    /**
     * 获取短业务名称.
     */
    public function getShortBusinessName(): string
    {
        return Str::camel(str_replace(
            Str::lower($this->tablesContract->getModuleName()),
            '',
            str_replace(env('DB_PREFIX'), '', $this->tablesContract->getTablename())
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
     * 获取模板地址
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . '/Api/main.stub';
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
    protected function placeholderReplace(): ApiGenerator
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
            '{LOAD_API}',
            '{COMMENT}',
            '{BUSINESS_NAME}',
            '{REQUEST_ROUTE}',
        ];
    }

    /**
     * 获取要替换占位符的内容.
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->getLoadApi(),
            $this->getComment(),
            $this->getBusinessName(),
            $this->getRequestRoute(),
        ];
    }

    protected function getLoadApi(): string
    {
        $menus = $this->tablesContract->getGenerateMenus() ? explode(',', $this->tablesContract->getGenerateMenus()) : [];
        $ignoreMenus = ['realDelete', 'recovery'];

        array_unshift($menus, $this->tablesContract->getType()->value === 'single' ? 'singleList' : 'treeList');

        foreach ($ignoreMenus as $menu) {
            if (in_array($menu, $menus)) {
                unset($menus[array_search($menu, $menus)]);
            }
        }

        $jsCode = '';
        $path = $this->getStubDir() . '/Api/';
        foreach ($menus as $menu) {
            $content = $this->filesystem->sharedGet($path . $menu . '.stub');
            $jsCode .= $content;
        }

        return $jsCode;
    }

    /**
     * 获取控制器注释.
     */
    protected function getComment(): string
    {
        return $this->getBusinessName() . ' API JS';
    }

    /**
     * 获取请求路由.
     */
    protected function getRequestRoute(): string
    {
        return Str::lower($this->tablesContract->getModuleName()) . '/' . $this->getShortBusinessName();
    }

    /**
     * 获取业务名称.
     */
    protected function getBusinessName(): string
    {
        return $this->tablesContract->getMenuName();
    }
}
