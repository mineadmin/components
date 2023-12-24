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

namespace Mine\Gateway;

/**
 * MineAdmin Store Application.
 */
class Application
{
    public function __construct(public array $config)
    {
        $this->initial();
    }

    // 初始化配置
    public function initial(): void {}

    private function checkConfig(): void
    {
        if (empty($this->config['server'])) {
        }
    }
}
