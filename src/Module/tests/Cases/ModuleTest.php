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

namespace Mine\Module\Tests\Cases;

use Mine\Module\Module;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ModuleTest extends TestCase
{
    public function testRemove(): void
    {
        Module::set('test', []);
        $this->assertTrue(Module::has('test'));
        Module::remove('test');
        $this->assertFalse(Module::has('test'));
    }
}
