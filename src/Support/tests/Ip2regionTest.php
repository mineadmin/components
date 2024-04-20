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

namespace Mine\Support\Tests;

use Hyperf\Contract\StdoutLoggerInterface;
use Mine\Support\Ip2region;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class Ip2regionTest extends TestCase
{
    protected Ip2region $ip2region;

    protected function setUp(): void
    {
        $logger = $this->createMock(StdoutLoggerInterface::class);
        $this->ip2region = new Ip2region($logger);
    }

    public function testSearch(): void
    {
        $ip = '127.0.0.1';
        $result = $this->ip2region->search($ip);
        $this->assertNotEmpty($result);

        $ip = '8.8.8.8'; // Google Public DNS IP
        $result = $this->ip2region->search($ip);
        $this->assertNotEmpty($result);

        $ip = '42.120.72.234'; // Example IP
        $result = $this->ip2region->search($ip);
        $this->assertNotEmpty($result);
    }

    public function testGetSearcher(): void
    {
        $searcher = $this->ip2region->getSearcher();
        $this->assertInstanceOf(\XdbSearcher::class, $searcher);
    }
}
