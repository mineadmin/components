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

namespace Mine\Module\Tests\Cases\Driver;

use Mine\Module\Driver\IniDriver;
use Mine\Module\Exception\ModuleConfigNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class IniDriverTest extends TestCase
{
    public function testWriteAndRead()
    {
        $yamlDriver = new IniDriver();
        $dir = dirname(__DIR__, 2) . '/Stub';
        $config = [
            'name' => 'test',
            'value' => 1,
        ];
        $yamlDriver->write($dir, $config);
        $this->assertEquals($yamlDriver->read($dir), $config);

        try {
            $yamlDriver->read(sys_get_temp_dir() . '/Test');
        } catch (\Exception $e) {
            $this->assertInstanceOf(ModuleConfigNotFoundException::class, $e);
        }
    }
}
