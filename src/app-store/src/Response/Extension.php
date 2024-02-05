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

namespace Xmo\AppStore\Response;

/**
 * 插件列表List.
 */
class Extension
{
    /**
     * @var string 团队/个人 id
     */
    public string $teamId;

    /**
     * @var string 插件名称
     */
    public string $name;

    /**
     * @var string 插件简介
     */
    public string $description;

    /**
     * @var ?string 插件首页
     */
    public ?string $homePage = null;

    /**
     * 插件类型 free(免费) charge(收费).
     */
    public string $type;

    /**
     * @var string 作者
     */
    public string $author;

    /**
     * @var string 价格
     */
    public string $amount;

    /**
     * @var int 下载量
     */
    public int $downloadTotal;

    /**
     * @var string 版本
     */
    public string $version;

    /**
     * @var string 延时
     */
    public string $example;
}
