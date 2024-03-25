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

namespace Mine\HttpServer\Tests\Cases\Log;

use Hyperf\Context\ApplicationContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\HttpServer\Log\RequestIdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RequestIdGeneratorTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testIdGenerator(): void
    {
        $generator = ApplicationContext::getContainer()->get(RequestIdGenerator::class);
        $id = $generator->generate();
        self::assertIsString($id);
        self::assertEquals($id, $generator->generate());
        Coroutine::create(function () use ($id) {
            $generator = ApplicationContext::getContainer()->get(RequestIdGenerator::class);
            self::assertNotEquals($id, $generator->generate());
        });
    }
}
