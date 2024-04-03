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

namespace Mine\Admin\Bundle\Tests\Cases;

use Mine\Admin\Bundle\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $this->assertIsArray((new ConfigProvider())());
    }
}
