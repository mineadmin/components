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

namespace Mine\SecurityBundle\Tests;

use Hyperf\Contract\ConfigInterface;
use Mine\SecurityBundle\Config;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigTest extends TestCase
{
    public function testGet()
    {
        $mock = \Mockery::mock(ConfigInterface::class);
        $mock->allows('get')->with('security.xxx', null)->andReturn('xxx');
        $config = new Config($mock);
        $this->assertEquals('xxx', $config->get('xxx'));
    }
}
