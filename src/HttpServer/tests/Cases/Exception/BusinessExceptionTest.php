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

namespace Mine\HttpServer\Tests\Cases\Exception;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Contract\TranslatorInterface;
use Mine\HttpServer\Constant\HttpResultCode;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Exception\BusinessException;
use Mine\HttpServer\Log\RequestIdGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * @internal
 * @coversNothing
 */
class BusinessExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        $config = new Config([
            StdoutLoggerInterface::class => [
                LogLevel::DEBUG,
            ],
        ]);
        ApplicationContext::getContainer()
            ->set(ConfigInterface::class, $config);
        ApplicationContext::getContainer()->define(
            RequestIdGeneratorInterface::class,
            RequestIdGenerator::class
        );
        ApplicationContext::getContainer()
            ->define(
                RequestIdGeneratorInterface::class,
                RequestIdGenerator::class
            );
    }

    public function testConstruct(): void
    {
        $translator = \Mockery::mock(TranslatorInterface::class);
        $translator->allows('trans')
            ->withArgs(function ($key) {
                return true;
            })
            ->andReturn('xxx');
        ApplicationContext::getContainer()->set(TranslatorInterface::class, $translator);
        $businessException = new BusinessException('xxx', 100);
        $this->assertSame($businessException->getMessage(), 'xxx');
        $this->assertSame($businessException->getCode(), 100);

        $businessException = new BusinessException(code: HttpResultCode::SUCCESS);
        $this->assertSame($businessException->getCode(), HttpResultCode::SUCCESS->value);
        $this->assertSame($businessException->getMessage(), 'xxx');
    }
}
