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
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Mine\Helper\AppVerify;
use Mine\Helper\LoginUser;
use Mine\MineCollection;
use Mine\Snowflake\SnowflakeIdGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

if (! function_exists('container')) {
    /**
     * 获取容器实例.
     */
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

if (! function_exists('redis')) {
    /**
     * 获取Redis实例.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function redis(): Redis
    {
        return container()->get(Redis::class);
    }
}

if (! function_exists('console')) {
    /**
     * 获取控制台输出实例.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function console(): StdoutLoggerInterface
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 获取日志实例.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function logger(string $name = 'Log'): LoggerInterface
    {
        return container()->get(LoggerFactory::class)->get($name);
    }
}

if (! function_exists('user')) {
    /**
     * 获取当前登录用户实例.
     */
    function user(string $scene = 'default'): LoginUser
    {
        return new LoginUser($scene);
    }
}

if (! function_exists('format_size')) {
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
}

if (! function_exists('lang')) {
    /**
     * 获取当前语言
     */
    function lang(): string
    {
        $acceptLanguage = container()
            ->get(RequestInterface::class)
            ->getHeaderLine('accept-language');
        return str_replace('-', '_', ! empty($acceptLanguage) ? explode(',', $acceptLanguage)[0] : 'zh_CN');
    }
}

if (! function_exists('t')) {
    /**
     * 多语言函数.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function t(string $key, array $replace = []): string
    {
        return \Hyperf\Translation\__($key, $replace, lang());
    }
}

if (! function_exists('mine_collect')) {
    /**
     * 创建一个Mine的集合类.
     * @param null|mixed $value
     */
    function mine_collect($value = null): MineCollection
    {
        return new MineCollection($value);
    }
}

if (! function_exists('context_set')) {
    /**
     * 设置上下文数据.
     * @param mixed $data
     */
    function context_set(string $key, $data): bool
    {
        return (bool) Context::set($key, $data);
    }
}

if (! function_exists('context_get')) {
    /**
     * 获取上下文数据.
     * @return mixed
     */
    function context_get(string $key)
    {
        return Context::get($key);
    }
}

if (! function_exists('app_verify')) {
    /**
     * 获取APP应用请求实例.
     */
    function app_verify(string $scene = 'api'): AppVerify
    {
        return new AppVerify($scene);
    }
}

if (! function_exists('snowflake_id')) {
    /**
     * 生成雪花ID.
     */
    function snowflake_id(): string
    {
        return (string) container()->get(SnowflakeIdGenerator::class)->generate();
    }
}

if (! function_exists('uuid')) {
    /**
     * 生成UUID.
     */
    function uuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}

if (! function_exists('event')) {
    /**
     * 事件调度快捷方法.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function event(object $dispatch): object
    {
        return container()->get(EventDispatcherInterface::class)->dispatch($dispatch);
    }
}

if (! function_exists('blank')) {
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

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (! function_exists('filled')) {
    /**
     * 判断给定的值是否不为空.
     */
    function filled(mixed $value): bool
    {
        return ! blank($value);
    }
}
