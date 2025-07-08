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

namespace Mine\Crontab\Cases;

use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\Crontab\CrontabContainer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CrontabContainerTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testContainer(): void
    {
        CrontabContainer::set('id', 'xxx');
        self::assertSame(CrontabContainer::get('id'), 'xxx');
        self::assertTrue(CrontabContainer::has('id'));
        self::assertFalse(CrontabContainer::has('test'));
    }
}
