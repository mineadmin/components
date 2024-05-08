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

use Hyperf\Contract\ApplicationInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Mine\Exception\NormalStatusException;
use Mine\Generator\Contracts\GeneratorTablesContract;
use Mine\Helper\Str;
use Mine\Interfaces\ServiceInterface\GenerateColumnServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

/**
 * 模型生成
 * Class ModelGenerator.
 */
class ModelGenerator extends MineGenerator implements CodeGenerator
{
    protected GeneratorTablesContract $tablesContract;

    protected string $codeContent;

    protected Filesystem $filesystem;

    /**
     * 设置生成信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setGenInfo(GeneratorTablesContract $tablesContract): ModelGenerator
    {
        $this->tablesContract = $tablesContract;
        $this->filesystem = make(Filesystem::class);
        if (empty($tablesContract->getModuleName()) || empty($tablesContract->getMenuName())) {
            throw new NormalStatusException(t('setting.gen_code_edit'));
        }
        $this->setNamespace($this->tablesContract->getNamespace());
        return $this;
    }

    /**
     * 生成代码
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generator(): void
    {
        $module = Str::title($this->tablesContract->getModuleName()[0]) . mb_substr($this->tablesContract->getModuleName(), 1);
        if ($this->tablesContract->getGenerateType()->value === 1) {
            $path = BASE_PATH . "/runtime/generate/php/app/{$module}/Model/";
        } else {
            $path = BASE_PATH . "/app/{$module}/Model/";
        }
        $this->filesystem->exists($path) || $this->filesystem->makeDirectory($path, 0755, true, true);

        $command = [
            'command' => 'mine:model-gen',
            '--module' => $this->tablesContract->getModuleName(),
            '--table' => str_replace(env('DB_PREFIX', ''), '', $this->tablesContract->getTableName()),
        ];

        if (! Str::contains($this->tablesContract->getTableName(), Str::lower($this->tablesContract->getModuleName()))) {
            throw new NormalStatusException(t('setting.gen_model_error'), 500);
        }

        if (mb_strlen($this->tablesContract->getTableName()) === mb_strlen($this->tablesContract->getModuleName())) {
            throw new NormalStatusException(t('setting.gen_model_error'), 500);
        }

        $input = new ArrayInput($command);
        $output = new NullOutput();

        /** @var Application $application */
        $application = $this->container->get(ApplicationInterface::class);
        $application->setAutoExit(false);

        $modelName = Str::studly(str_replace(env('DB_PREFIX', ''), '', $this->tablesContract->getTableName()));

        if ($application->run($input, $output) === 0) {
            // 对模型文件处理
            $modelName = \Hyperf\Stringable\Str::singular($modelName);
            if ($modelName[strlen($modelName) - 1] == 's' && $modelName[strlen($modelName) - 2] != 's') {
                $oldName = Str::substr($modelName, 0, strlen($modelName) - 1);
                $oldPath = BASE_PATH . "/app/{$module}/Model/{$oldName}.php";
                $sourcePath = BASE_PATH . "/app/{$module}/Model/{$modelName}.php";
                $this->filesystem->put(
                    $sourcePath,
                    str_replace($oldName, $modelName, $this->filesystem->sharedGet($oldPath))
                );
                @unlink($oldPath);
            } else {
                $sourcePath = BASE_PATH . "/app/{$module}/Model/{$modelName}.php";
            }

            if (! empty($this->tablesContract->options['relations'])) {
                $this->filesystem->put(
                    $sourcePath,
                    preg_replace('/}$/', $this->getRelations() . '}', $this->filesystem->sharedGet($sourcePath))
                );
            }

            // 压缩包下载
            if ($this->tablesContract->getGenerateType()->value === 1) {
                $toPath = BASE_PATH . "/runtime/generate/php/app/{$module}/Model/{$modelName}.php";

                $isFile = is_file($sourcePath);

                if ($isFile) {
                    $this->filesystem->copy($sourcePath, $toPath);
                } else {
                    $this->filesystem->move($sourcePath, $toPath);
                }
            }
        } else {
            throw new NormalStatusException(t('setting.gen_model_error'), 500);
        }
    }

    /**
     * 预览代码
     */
    public function preview(): string
    {
        return $this->placeholderReplace()->getCodeContent();
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
     * 获取控制器模板地址
     */
    protected function getTemplatePath(): string
    {
        return $this->getStubDir() . 'model.stub';
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
    protected function placeholderReplace(): ModelGenerator
    {
        $this->setCodeContent(str_replace(
            $this->getPlaceHolderContent(),
            $this->getReplaceContent(),
            $this->readTemplate(),
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
            '{CLASS_NAME}',
            '{TABLE_NAME}',
            '{FILL_ABLE}',
            '{RELATIONS}',
        ];
    }

    /**
     * 获取要替换占位符的内容.
     */
    protected function getReplaceContent(): array
    {
        return [
            $this->initNamespace(),
            $this->getClassName(),
            $this->getTableName(),
            $this->getFillAble(),
            $this->getRelations(),
        ];
    }

    /**
     * 初始化模型命名空间.
     */
    protected function initNamespace(): string
    {
        return $this->getNamespace() . '\\Model';
    }

    /**
     * 获取类名称.
     */
    protected function getClassName(): string
    {
        return $this->getBusinessName();
    }

    /**
     * 获取表名称.
     */
    protected function getTableName(): string
    {
        return $this->tablesContract->getTableName();
    }

    /**
     * 获取file able.
     */
    protected function getFillAble(): string
    {
        //        $data = make(GenerateColumnServiceInterface::class)->getList(
        //            ['select' => 'column_name', 'table_id' => $this->tablesContract->id]
        //        );
        $data = array_column($this->tablesContract->getColumns()->toArray(), 'column_name');
        $columns = [];
        foreach ($data as $column) {
            $columns[] = "'" . $column . "'";
        }

        return implode(', ', $columns);
    }

    protected function getRelations(): string
    {
        $prefix = env('DB_PREFIX', '');
        if (! empty($this->tablesContract->getOptions()['relations'])) {
            $path = $this->getStubDir() . 'ModelRelation/';
            $phpCode = '';
            foreach ($this->tablesContract->getOptions()['relations'] as $relation) {
                $content = $this->filesystem->sharedGet($path . $relation['type'] . '.stub');
                $content = str_replace(
                    ['{RELATION_NAME}', '{MODEL_NAME}', '{TABLE_NAME}', '{FOREIGN_KEY}', '{LOCAL_KEY}'],
                    [$relation['name'], $relation['model'], str_replace($prefix, '', $relation['table']), $relation['foreignKey'], $relation['localKey']],
                    $content
                );
                $phpCode .= $content;
            }
            return $phpCode;
        }
        return '';
    }
}
