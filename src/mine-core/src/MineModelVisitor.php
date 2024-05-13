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

namespace Mine;

use Hyperf\Database\Commands\Ast\GenerateModelIDEVisitor;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Mine\Annotation\DependProxy;
use Mine\Helper\Str;

use function Hyperf\Support\class_basename;

/**
 * Class MineModelVisitor.
 */
#[DependProxy(values: [ModelUpdateVisitor::class])]
class MineModelVisitor extends ModelUpdateVisitor
{
    protected function formatDatabaseType(string $type): ?string
    {
        return match ($type) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => 'integer',
            'decimal' => 'decimal:2',
            'float', 'double', 'real' => 'float',
            'bool', 'boolean' => 'boolean',
            'json' => 'array',
            default => null,
        };
    }

    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        switch ($cast) {
            case 'integer':
                return 'int';
            case 'date':
            case 'datetime':
                return '\Carbon\Carbon';
            case 'json':
                return 'array';
        }

        if (Str::startsWith($cast, 'decimal')) {
            // 如果 cast 为 decimal，则 @property 改为 string
            return 'string';
        }

        return $cast;
    }

    protected function parse(): string
    {
        $doc = '/**' . PHP_EOL;
        $doc = $this->parseProperty($doc);
        $doc = $this->parseMethod($doc);
        if ($this->option->isWithIde()) {
            $doc .= ' * @mixin \\' . GenerateModelIDEVisitor::toIDEClass(get_class($this->class)) . PHP_EOL;
        }
        $doc .= ' */';
        return $doc;
    }

    protected function parseMethod(string $doc): string
    {
        foreach ($this->columns as $column) {
            [$name] = $this->getProperty($column);
            $methodName = 'where' . ucfirst(Str::studly($name)) . '($value)';
            $hyperfBuilderNameSpace = '\Hyperf\Database\Model\Builder';
            $className = class_basename($this->class);
            $doc .= sprintf(' * @method %s %s', "{$hyperfBuilderNameSpace}|{$className}", $methodName) . PHP_EOL;
        }
        return $doc;
    }
}
