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

namespace Mine\Support;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

use function Hyperf\Translation\__;

/**
 * 获取容器实例.
 */
function container(): ContainerInterface
{
    return ApplicationContext::getContainer();
}

/**
 * 获取Redis实例.
 */
function redis(): \Redis
{
    return container()->get(\Redis::class);
}

function console(): StdoutLoggerInterface
{
    return container()->get(StdoutLoggerInterface::class);
}

/**
 * 获取日志实例.
 */
function logger(string $name = 'Log'): LoggerInterface
{
    return container()->get(LoggerFactory::class)->get($name);
}

/**
 * 格式化大小.
 */
function format_size(int $size): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $index = 0;
    for ($i = 0; $size >= 1024 && $i < 5; ++$i) {
        $size /= 1024;
        $index = $i;
    }
    return round($size, 2) . $units[$index];
}

/**
 * 多语言函数.
 */
function t(string $key, array $replace = []): string
{
    return __($key, $replace);
}

/**
 * 设置上下文数据.
 */
function context_set(string $key, mixed $data): bool
{
    return (bool) Context::set($key, $data);
}

/**
 * 获取上下文数据.
 */
function context_get(string $key): mixed
{
    return Context::get($key);
}

/**
 * 生成雪花ID.
 */
function snowflake_id(): string
{
    return (string) container()
        ->get(SnowflakeIdGenerator::class)
        ->generate();
}

/**
 * 生成UUID.
 */
function uuid(): string
{
    return Uuid::uuid4()->toString();
}

/**
 * 事件调度快捷方法.
 */
function event(object $dispatch): object
{
    return container()
        ->get(EventDispatcherInterface::class)
        ->dispatch($dispatch);
}

/**
 * 判断给定的值是否为空.
 */
function blank(mixed $value): bool
{
    if (is_null($value)) {
        return true;
    }

    if (is_string($value)) {
        return trim($value) === '';
    }

    if (is_numeric($value) || is_bool($value)) {
        return false;
    }

    if ($value instanceof \Countable) {
        return count($value) === 0;
    }

    return empty($value);
}

/**
 * 判断给定的值是否不为空.
 */
function filled(mixed $value): bool
{
    return ! blank($value);
}
