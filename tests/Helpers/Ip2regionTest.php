<?php

namespace Mine\Tests\Helpers;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Testing\TestCase;
use Mine\Helper\Ip2region;

class Ip2regionTest extends \PHPUnit\Framework\TestCase
{
    public function testMake(): void
    {
        $ip2region = new Ip2region();
        $this->assertInstanceOf(Ip2region::class,$ip2region);
    }

    public function testSearch()
    {
        $ip2region = new Ip2region();
        $result = $ip2region->search('114.114.114.114');
        $this->assertIsString($result);
    }
}