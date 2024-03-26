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

namespace Mine\HttpServer\Tests\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Di\Exception\Exception;
use Mine\HttpServer\Constant\HttpResultCode;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Log\RequestIdGenerator;
use Mine\HttpServer\RequestIdHolder;
use Mine\HttpServer\Result;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * @internal
 * @coversNothing
 */
class ResultTest extends TestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        ApplicationContext::setContainer(new Container((new DefinitionSourceFactory())()));
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
    }

    public function testConstruct(): void
    {
        $result = new Result(
            true,
            'xxx',
            HttpResultCode::SUCCESS,
        );
        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame($result->message, 'xxx');
        $this->assertSame($result->getMessage(), 'xxx');
        $this->assertNull($result->getData());
        $this->assertNull($result->data);
        $this->assertSame($result->getCode(), HttpResultCode::SUCCESS->value);
        $this->assertSame($result->code, HttpResultCode::SUCCESS->value);

        $result->setMessage('xx2');
        $this->assertSame($result->message, 'xx2');
        $this->assertSame($result->getMessage(), 'xx2');

        $result->setCode(HttpResultCode::FAILED);
        $this->assertSame($result->getCode(), HttpResultCode::FAILED->value);
        $this->assertSame($result->code, HttpResultCode::FAILED->value);

        $result->setData(['xxx']);
        $this->assertSame($result->getData(), ['xxx']);
        $this->assertSame($result->data, ['xxx']);
        $this->assertSame($result->toArray(), [
            'success' => true,
            'requestId' => RequestIdHolder::getId(),
            'message' => 'xx2',
            'data' => [
                'xxx',
            ],
            'code' => HttpResultCode::FAILED->value,
        ]);
    }

    public function testSuccess(): void
    {
        $result = Result::success(
            'xxx',
            ['xxx'],
            HttpResultCode::SUCCESS
        );
        $this->assertSame($result->getMessage(), 'xxx');
        $this->assertSame($result->getData(), ['xxx']);
        $this->assertTrue($result->isSuccess());
        $this->assertSame($result->data, ['xxx']);
        $this->assertSame($result->getCode(), HttpResultCode::SUCCESS->value);
    }

    public function testFailed(): void
    {
        $result = Result::error(
            'xxx',
            ['xxx'],
            HttpResultCode::FAILED
        );
        $this->assertSame($result->getMessage(), 'xxx');
        $this->assertSame($result->getData(), ['xxx']);
        $this->assertFalse($result->isSuccess());
        $this->assertSame($result->data, ['xxx']);
        $this->assertSame($result->getCode(), HttpResultCode::FAILED->value);
    }
}
