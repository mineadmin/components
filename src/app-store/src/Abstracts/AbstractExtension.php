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

namespace Xmo\AppStore\Abstracts;

use Hyperf\Contract\ContainerInterface;

/**
 * 插件基类.
 */
abstract class AbstractExtension
{
    /**
     * 插件名称.
     */
    public string $name;

    /**
     * 插件简介.
     */
    public string $description;

    /**
     * 主页.
     */
    public string $homePage;

    public function __construct(
        public ContainerInterface $container
    ) {}

    /**
     * Plug-in boot method, system level
     * This method will be called in the Hyperf BootApplication event after the plugin is installed
     * That is, before all events are executed.
     */
    public static function boot(): void {}

    /**
     * Plugin register method, system level
     * This method will be called in the Hyperf Server MainServer startup event after the plugin is installed.
     * Parameter isCoroutine is used to determine whether the current is a Concurrent Server or not.
     */
    public static function register(bool $isCoroutine): void {}

    /**
     * 插件安装方法，此方法用作插件安装时执行，返回 true 则代表安装成功
     * 返回 false 或 抛出异常则代表安装失败。
     * 此方法默认在 数据库事务中执行.
     */
    abstract public static function install(): bool;

    /**
     * 插件卸载方法，此方法用作插件安装时执行，返回 true 则代表成功
     * 返回 false 或 抛出异常则代表失败。
     * 此方法默认在 数据库事务中执行.
     */
    abstract public static function uninstall(): bool;
}
