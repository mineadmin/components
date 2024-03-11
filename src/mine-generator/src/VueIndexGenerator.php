<?php
/**
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

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Support\Filesystem\Filesystem;
use Mine\Exception\NormalStatusException;
use Mine\Generator\Contracts\GeneratorTablesContract;
use Mine\Generator\Enums\ComponentTypeEnum;
use Mine\Helper\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * Vue index文件生成
 * Class VueIndexGenerator.
 */
class VueIndexGenerator extends MineGenerator implements CodeGenerator
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
    public function setGenInfo(GeneratorTablesContract $tablesContract): VueIndexGenerator
    {
        $this->tablesContract = $tablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($tablesContract->getModuleName()) || empty($tablesContract->getMenuName())) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->columns = $this->tablesContract->handleQuery(function (Builder $query) {
            return $query->where('table_id', $this->tablesContract->getId())->orderByDesc('sort')
                ->get([
                    'column_name', 'column_comment', 'allow_roles', 'options', 'is_required', 'is_insert',
                    'is_edit', 'is_query', 'is_sort', 'is_pk', 'is_list', 'view_type', 'dict_type',
                ]);
        });
        return $this->placeholderReplace();
    }

    /**
     * 生成代码
     */
    public function generator(): void
    {
        $module = Str::lower($this->tablesContract->getModuleName());
        $path = BASE_PATH . "/runtime/generate/vue/src/views/{$module}/{$this->getShortBusinessName()}/index.vue";
        $this->filesystem->makeDirectory(
            BASE_PATH . "/runtime/generate/vue/src/views/{$module}/{$this->getShortBusinessName()}",
            0755,
            true,
            true
        );
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
            str_replace(env('DB_PREFIX',''), '', $this->tablesContract->getTableName())
        ));
    }

    /**
     * 获取组件类型.
     */
    public function getComponentType(int $type): string
    {
        return match ($type) {
            2 => "'drawer'",
            3 => "'tag'",
            default => "'modal'"
        };
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
        return $this->getStubDir() . '/Vue/index.stub';
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
    protected function placeholderReplace(): VueIndexGenerator
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
            '{CODE}',
            '{OPTIONS}',
            '{COLUMNS}',
            '{BUSINESS_EN_NAME}',
            '{INPUT_NUMBER}',
            '{SWITCH_STATUS}',
            '{MODULE_NAME}',
            '{PK}',
        ];
    }

    /**
     * 获取要替换占位符的内容.
     * @return string[]
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->getCode(),
            $this->getOptions(),
            $this->getColumns(),
            $this->getBusinessEnName(),
            $this->getInputNumber(),
            $this->getSwitchStatus(),
            $this->getModuleName(),
            $this->getPk(),
        ];
    }

    /**
     * 获取标识代码
     */
    protected function getCode(): string
    {
        return Str::lower($this->tablesContract->getModuleName()) . ':' . $this->getShortBusinessName();
    }

    /**
     * 获取CRUD配置代码
     */
    protected function getOptions(): string
    {
        // 配置项
        $options = [];
        $options['id'] = "'" . $this->tablesContract->getTableName() . "'";
        $options['rowSelection'] = ['showCheckedAll' => true];
        $options['pk'] = "'" . $this->getPk() . "'";
        $options['operationColumn'] = false;
        $options['operationColumnWidth'] = 160;
        $options['formOption'] = [
            'viewType' => "'{$this->tablesContract->getComponentType()->value}'",
            'width' => 600,
        ];
        if ($this->tablesContract->getComponentType() === ComponentTypeEnum::TAG) {
            $options['formOption']['tagId'] = "'" . ($this->tablesContract->options['tag_id'] ?? $this->tablesContract->getTableName()) . "'";
            $options['formOption']['tagName'] = "'" . ($this->tablesContract->options['tag_name'] ?? $this->tablesContract->table_comment) . "'";
            $options['formOption']['titleDataIndex'] = "'" . ($this->tablesContract->options['tag_view_name'] ?? $this->getPk()) . "'";
        }
        $options['api'] = $this->getBusinessEnName() . '.getList';
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'recycle')) {
            $options['recycleApi'] = $this->getBusinessEnName() . '.getRecycleList';
        }
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'save')) {
            $options['add'] = [
                'show' => true, 'api' => $this->getBusinessEnName() . '.save',
                'auth' => "['" . $this->getCode() . ":save']",
            ];
        }
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'update')) {
            $options['operationColumn'] = true;
            $options['edit'] = [
                'show' => true, 'api' => $this->getBusinessEnName() . '.update',
                'auth' => "['" . $this->getCode() . ":update']",
            ];
        }
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'delete')) {
            $options['operationColumn'] = true;
            $options['delete'] = [
                'show' => true,
                'api' => $this->getBusinessEnName() . '.deletes',
                'auth' => "['" . $this->getCode() . ":delete']",
            ];
            if (Str::contains($this->tablesContract->getGenerateMenus(), 'recycle')) {
                $options['delete']['realApi'] = $this->getBusinessEnName() . '.realDeletes';
                $options['delete']['realAuth'] = "['" . $this->getCode() . ":realDeletes']";
                $options['recovery'] = [
                    'show' => true,
                    'api' => $this->getBusinessEnName() . '.recoverys',
                    'auth' => "['" . $this->getCode() . ":recovery']",
                ];
            }
        }
        $requestRoute = Str::lower($this->tablesContract->getModuleName()) . '/' . $this->getShortBusinessName();
        // 导入
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'import')) {
            $options['import'] = [
                'show' => true,
                'url' => "'" . $requestRoute . '/import' . "'",
                'templateUrl' => "'" . $requestRoute . '/downloadTemplate' . "'",
                'auth' => "['" . $this->getCode() . ":import']",
            ];
        }
        // 导出
        if (Str::contains($this->tablesContract->getGenerateMenus(), 'export')) {
            $options['export'] = [
                'show' => true,
                'url' => "'" . $requestRoute . '/export' . "'",
                'auth' => "['" . $this->getCode() . ":export']",
            ];
        }
        return 'const options = reactive(' . $this->jsonFormat($options, true) . ')';
    }

    /**
     * 获取列配置代码
     */
    protected function getColumns(): string
    {
        // 字段配置项
        $options = [];
        foreach ($this->columns as $column) {
            $tmp = [
                'title' => $column->column_comment,
                'dataIndex' => $column->column_name,
                'formType' => $this->getViewType($column->view_type),
            ];
            // 基础
            if ($column->is_query == self::YES) {
                $tmp['search'] = true;
            }
            if ($column->is_insert == self::NO) {
                $tmp['addDisplay'] = false;
            }
            if ($column->is_edit == self::NO) {
                $tmp['editDisplay'] = false;
            }
            if ($column->is_list == self::NO) {
                $tmp['hide'] = true;
            }
            if ($column->is_required == self::YES) {
                $tmp['commonRules'] = [
                    'required' => true,
                    'message' => '请输入' . $column->column_comment,
                ];
            }
            if ($column->is_sort == self::YES) {
                $tmp['sortable'] = [
                    'sortDirections' => ['ascend', 'descend'],
                    'sorter' => true,
                ];
            }
            // 扩展项
            if (! empty($column->options)) {
                $collection = $column->options['collection'] ?? [];
                // 合并
                $tmp = array_merge($tmp, $column->options);
                // 自定义数据
                if (in_array($column->view_type, ['checkbox', 'radio', 'select', 'transfer']) && ! empty($collection)) {
                    $tmp['dict'] = ['data' => $collection, 'translation' => true];
                }
                // 对日期时间处理
                if ($column->view_type === 'date' && $column->options['mode'] === 'date') {
                    unset($tmp['mode']);
                    if (isset($column->options['range']) && $column->options['range']) {
                        $tmp['formType'] = 'range';
                        unset($tmp['range']);
                    }
                }
                unset($tmp['collection']);
            }
            // 字典
            if (! empty($column->dict_type)) {
                $tmp['dict'] = [
                    'name' => $column->dict_type,
                    'props' => ['label' => 'title', 'value' => 'key'],
                    'translation' => true,
                ];
            }
            // 密码处理
            if ($column->view_type === 'password') {
                $tmp['type'] = 'password';
            }
            // 允许查看字段的角色（前端还待支持）
            // todo...
            $options[] = $tmp;
        }
        return 'const columns = reactive(' . $this->jsonFormat($options) . ')';
    }

    protected function getShowRecycle(): string
    {
        return (strpos($this->tablesContract->getGenerateMenus(), 'recycle') > 0) ? 'true' : 'false';
    }

    /**
     * 获取业务英文名.
     */
    protected function getBusinessEnName(): string
    {
        return Str::camel(str_replace(env('DB_PREFIX',''), '', $this->tablesContract->getTableName()));
    }

    protected function getModuleName(): string
    {
        return Str::lower($this->tablesContract->getModuleName());
    }

    /**
     * 返回主键.
     */
    protected function getPk(): string
    {
        foreach ($this->columns as $column) {
            if ($column->is_pk == self::YES) {
                return $column->column_name;
            }
        }
        return '';
    }

    /**
     * 计数器组件方法.
     * @noinspection BadExpressionStatementJS
     */
    protected function getInputNumber(): string
    {
        if (in_array('numberOperation', explode(',', $this->tablesContract->getGenerateMenus()))) {
            return str_replace('{BUSINESS_EN_NAME}', $this->getBusinessEnName(), $this->getOtherTemplate('numberOperation'));
        }
        return '';
    }

    /**
     * 计数器组件方法.
     * @noinspection BadExpressionStatementJS
     */
    protected function getSwitchStatus(): string
    {
        if (in_array('changeStatus', explode(',', $this->tablesContract->getGenerateMenus()))) {
            return str_replace('{BUSINESS_EN_NAME}', $this->getBusinessEnName(), $this->getOtherTemplate('switchStatus'));
        }
        return '';
    }

    protected function getOtherTemplate(string $tpl): string
    {
        return $this->filesystem->sharedGet($this->getStubDir() . "/Vue/{$tpl}.stub");
    }

    /**
     * 视图组件.
     */
    protected function getViewType(string $viewType): string
    {
        $viewTypes = [
            'text' => 'input',
            'password' => 'input-password',
            'textarea' => 'textarea',
            'inputNumber' => 'input-number',
            'inputTag' => 'input-tag',
            'mention' => 'mention',
            'switch' => 'switch',
            'slider' => 'slider',
            'select' => 'select',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'treeSelect' => 'tree-select',
            'date' => 'date',
            'time' => 'time',
            'rate' => 'rate',
            'cascader' => 'cascader',
            'transfer' => 'transfer',
            'selectUser' => 'user-select',
            'userInfo' => 'user-info',
            'cityLinkage' => 'city-linkage',
            'icon' => 'icon-picker',
            'formGroup' => 'form-group',
            'upload' => 'upload',
            'selectResource' => 'resource',
            'editor' => 'editor',
            'wangEditor' => 'wang-editor',
            'codeEditor' => 'code-editor',
        ];

        return $viewTypes[$viewType] ?? 'input';
    }

    /**
     * array 到 json 数据格式化.
     */
    protected function jsonFormat(array $data, bool $removeValueQuotes = false): string
    {
        $data = str_replace('    ', '  ', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $data = str_replace(['"true"', '"false"', '\\'], [true, false, ''], $data);
        $data = preg_replace('/(\s+)\"(.+)\":/', '\\1\\2:', $data);
        if ($removeValueQuotes) {
            $data = preg_replace('/(:\s)\"(.+)\"/', '\\1\\2', $data);
        }
        return $data;
    }
}
