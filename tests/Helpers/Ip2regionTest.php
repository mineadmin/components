<?php

namespace Mine\Tests\Helpers;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Testing\TestCase;
use Mine\Helper\Ip2region;

class Ip2regionTest extends TestCase
{
    public function testMake(): void
    {
        $ip2region = new Ip2region(ApplicationContext::getContainer()->get(StdoutLoggerInterface::class));
        $this->assertInstanceOf(Ip2region::class,$ip2region);
    }
}