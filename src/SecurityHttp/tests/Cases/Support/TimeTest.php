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

namespace Mine\Security\Http\Tests\Cases\Support;

use Carbon\Carbon;
use Mine\Security\Http\Support\Time;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TimeTest extends TestCase
{
    public function testNowReturnsCarbonInstance(): void
    {
        $time = Time::now();

        $this->assertInstanceOf(Carbon::class, $time);
        $this->assertSame('UTC', $time->getTimezone()->getName());
    }

    public function testTimestampReturnsCarbonInstance(): void
    {
        $timestamp = 1630000000;
        $time = Time::timestamp($timestamp);

        $this->assertInstanceOf(Carbon::class, $time);
        $this->assertSame('UTC', $time->getTimezone()->getName());
        $this->assertSame($timestamp, $time->getTimestamp());
    }

    public function testIsPastReturnsTrueForPastTimestamp(): void
    {
        $timestamp = time() - 60; // 60 seconds ago
        $result = Time::isPast($timestamp);

        $this->assertTrue($result);
    }

    public function testIsPastReturnsFalseForFutureTimestamp(): void
    {
        $timestamp = time() + 60; // 60 seconds later
        $result = Time::isPast($timestamp);

        $this->assertFalse($result);
    }

    public function testIsPastReturnsFalseForCurrentTimestampWithLeeway(): void
    {
        $timestamp = time();
        $leeway = 60; // 60 seconds
        $result = Time::isPast($timestamp, $leeway);

        $this->assertFalse($result);
    }

    public function testIsFutureReturnsTrueForFutureTimestamp(): void
    {
        $timestamp = time() + 60; // 60 seconds later
        $result = Time::isFuture($timestamp);

        $this->assertTrue($result);
    }

    public function testIsFutureReturnsFalseForPastTimestamp(): void
    {
        $timestamp = time() - 60; // 60 seconds ago
        $result = Time::isFuture($timestamp);

        $this->assertFalse($result);
    }

    public function testIsFutureReturnsFalseForCurrentTimestampWithLeeway(): void
    {
        $timestamp = time();
        $leeway = 60; // 60 seconds
        $result = Time::isFuture($timestamp, $leeway);

        $this->assertFalse($result);
    }
}
