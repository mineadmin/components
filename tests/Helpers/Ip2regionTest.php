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

namespace Mine\Tests\Helpers;

use Mine\Helper\Ip2region;

/**
 * @internal
 * @coversNothing
 */
class Ip2regionTest extends \PHPUnit\Framework\TestCase
{
    public function testMake(): void
    {
        $ip2region = new Ip2region();
        $this->assertInstanceOf(Ip2region::class, $ip2region);
    }

    public function testSearch()
    {
        $ip2region = new Ip2region();
        $result = $ip2region->search('114.114.114.114');
        $this->assertIsString($result);
    }
}
