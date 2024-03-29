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

namespace Mine\SecurityBundle\Tests\Context;

use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\SecurityBundle\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConTextTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testAll(): void
    {
        $context = new Context();
        $context->set('key', 'value');
        $this->assertEquals('value', $context->get('key'));
        $this->assertEquals(null, $context->get('not_exist'));
        $this->assertEquals('xxx', $context->get('not_exist', 'xxx'));
        $this->assertEquals('xxx', $context->getOrSet('not_exist', 'xxx'));
        $this->assertEquals('xxx2', $context->getOrSet('not_exist1', function () {
            return 'xxx2';
        }));
        $this->assertTrue($context->has('key'));
        $this->assertFalse($context->has('key2'));
    }
}
