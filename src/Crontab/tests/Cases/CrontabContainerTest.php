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

namespace Mine\Crontab\Tests\Cases;

use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\Crontab\CrontabContainer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabContainerTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testContainer(): void
    {
        CrontabContainer::set('id', 'xxx');
        $this->assertSame(CrontabContainer::get('id'), 'xxx');
        $this->assertTrue(CrontabContainer::has('id'));
        $this->assertFalse(CrontabContainer::has('test'));
    }
}
