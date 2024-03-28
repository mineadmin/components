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

namespace Mine\Security\Http\Support;

use Carbon\Carbon;

class Time
{
    /**
     * Get the Carbon instance for the current time.
     */
    public static function now(): Carbon
    {
        return Carbon::now('UTC');
    }

    /**
     * Get the Carbon instance for the timestamp.
     */
    public static function timestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestampUTC($timestamp)->timezone('UTC');
    }

    /**
     * Checks if a timestamp is in the past.
     */
    public static function isPast(int $timestamp, int $leeway = 0): bool
    {
        return static::timestamp($timestamp)->addSeconds($leeway)->isPast();
    }

    /**
     * Checks if a timestamp is in the future.
     */
    public static function isFuture(int $timestamp, int $leeway = 0): bool
    {
        return static::timestamp($timestamp)->subSeconds($leeway)->isFuture();
    }
}
