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

namespace Mine\Helper;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder as ModelBuilder;
use Hyperf\Database\Query\Builder;

final class FastBuilderWhere
{
    public function __construct(
        private readonly Builder|ModelBuilder $builder,
        private readonly array $params,
    ) {}

    public function eq(string $column, ?string $key = null): self
    {
        return $this->buildOperator('=', $column, $key);
    }

    public function lt(string $column, ?string $key = null): self
    {
        return $this->buildOperator('<', $column, $key);
    }

    public function ne(string $column, ?string $key = null): self
    {
        return $this->buildOperator('<>', $column, $key);
    }

    public function le(string $column, ?string $key = null): self
    {
        return $this->buildOperator('<=', $column, $key);
    }

    public function ge(string $column, ?string $key = null): self
    {
        return $this->buildOperator('>=', $column, $key);
    }

    public function gt(string $column, ?string $key = null): self
    {
        return $this->buildOperator('>', $column, $key);
    }

    public function like(string $column, ?string $key = null): self
    {
        return $this->buildOperator('like', $column, $key, function (Builder|ModelBuilder $builder, string $column, mixed $value) {
            return $builder->where($column, 'like', '%' . $value . '%');
        });
    }

    public function likeRight(string $column, ?string $key = null): self
    {
        return $this->buildOperator('like', $column, $key, function (Builder|ModelBuilder $builder, string $column, mixed $value) {
            return $builder->where($column, 'like', $value . '%');
        });
    }

    public function likeLeft(string $column, ?string $key = null): self
    {
        return $this->buildOperator('like', $column, $key, function (Builder|ModelBuilder $builder, string $column, mixed $value) {
            return $builder->where($column, 'like', '%' . $value);
        });
    }

    public function timestampsRange(string $column, array|string $keys): self
    {
        return $this->where(
            $column,
            function (Builder|ModelBuilder $builder) use ($keys, $column) {
                [$start,$end] = $this->getRangeValues($keys);
                return $builder->whereBetween(
                    $column,
                    [
                        $start instanceof CarbonInterface ? $start->timestamp : $start,
                        $end instanceof CarbonInterface ? $end->timestamp : $end,
                    ]
                );
            },
            ''
        );
    }

    public function datetimeRange(string $column, array|string $keys): self
    {
        return $this->where(
            $column,
            function (Builder|ModelBuilder $builder) use ($keys, $column) {
                [$start,$end] = $this->getRangeValues($keys);
                return $builder->whereBetween(
                    $column,
                    [
                        $start instanceof CarbonInterface ? $start : Carbon::createFromFormat(CarbonInterface::DEFAULT_TO_STRING_FORMAT, $start),
                        $end instanceof CarbonInterface ? $end : Carbon::createFromFormat(CarbonInterface::DEFAULT_TO_STRING_FORMAT, $end),
                    ]
                );
            },
            ''
        );
    }

    public function dateRange(string $column, array|string $keys): self
    {
        return $this->where(
            $column,
            function (Builder|ModelBuilder $builder) use ($keys, $column) {
                [$start,$end] = $this->getRangeValues($keys);
                return $builder->whereBetween(
                    $column,
                    [
                        $start instanceof CarbonInterface ? $start->startOfDay() : Carbon::createFromFormat('Y-m-d', $start)->startOfDay(),
                        $end instanceof CarbonInterface ? $end->endOfDay() : Carbon::createFromFormat('Y-m-d', $end)->endOfDay(),
                    ]
                );
            },
            ''
        );
    }

    public function getBuilder(): Builder|ModelBuilder
    {
        return $this->builder;
    }

    private function buildOperator(string $operator, string $column, ?string $key, ?\Closure $next = null): self
    {
        return $this->where(
            $column,
            function (Builder|ModelBuilder $builder, mixed $value) use ($operator, $column, $next) {
                if ($next) {
                    return $next($builder, $column, $value);
                }
                return $builder->where($column, $operator, $value);
            },
            $key
        );
    }

    private function where(string $column, \Closure $closure, ?string $key): self
    {
        $this->builder->when(Arr::get($this->params, $this->getKey($column, $key)), $closure);
        return $this;
    }

    private function getKey(string $column, ?string $key): string
    {
        return $key ?: $column;
    }

    private function getRangeValues(array|string $keys): ?array
    {
        if (is_string($keys)) {
            return Arr::get($this->params, $keys);
        }
        return [Arr::get($this->params, $keys[0]), Arr::get($this->params, $keys[1])];
    }
}
