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

namespace Mine\Generator\Traits;

trait MapperGeneratorTraits
{
    /**
     * 获取搜索代码
     * @param mixed $column
     */
    protected function getSearchCode($column): string
    {
        return match ($column['query_type']) {
            'neq' => $this->getSearchPHPString($column['column_name'], '!=', $column['column_comment']),
            'gt' => $this->getSearchPHPString($column['column_name'], '<', $column['column_comment']),
            'gte' => $this->getSearchPHPString($column['column_name'], '<=', $column['column_comment']),
            'lt' => $this->getSearchPHPString($column['column_name'], '>', $column['column_comment']),
            'lte' => $this->getSearchPHPString($column['column_name'], '>=', $column['column_comment']),
            'like' => $this->getSearchPHPString($column['column_name'], 'like', $column['column_comment']),
            'between' => $this->getSearchPHPString($column['column_name'], 'between', $column['column_comment']),
            'in' => $this->getSearchPHPString($column['column_name'], 'in', $column['column_comment']),
            'notin' => $this->getSearchPHPString($column['column_name'], 'notin', $column['column_comment']),
            default => $this->getSearchPHPString($column['column_name'], '=', $column['column_comment']),
        };
    }

    protected function getSearchPHPString($name, $mark, $comment): string
    {
        if ($mark == 'like') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && filled(\$params['{$name}'])) {
            \$query->where('{$name}', 'like', '%'.\$params['{$name}'].'%');
        }

php;
        }

        if ($mark == 'between') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && filled(\$params['{$name}']) && is_array(\$params['{$name}']) && count(\$params['{$name}']) == 2) {
            \$query->whereBetween(
                '{$name}',
                [ \$params['{$name}'][0], \$params['{$name}'][1] ]
            );
        }

php;
        }

        if ($mark == 'in') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && filled(\$params['{$name}'])) {
            \$query->whereIn('{$name}', \$params['{$name}']);
        }

php;
        }

        if ($mark == 'notin') {
            return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && filled(\$params['{$name}'])) {
            \$query->whereNotIn('{$name}', \$params['{$name}']);
        }

php;
        }

        return <<<php

        // {$comment}
        if (isset(\$params['{$name}']) && filled(\$params['{$name}'])) {
            \$query->where('{$name}', '{$mark}', \$params['{$name}']);
        }

php;
    } // 该方法结束位置
}
