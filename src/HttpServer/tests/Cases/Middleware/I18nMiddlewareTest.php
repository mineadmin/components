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

namespace Mine\HttpServer\Tests\Cases\Middleware;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Mine\HttpServer\Log\RequestIdGenerator;
use Mine\HttpServer\Middleware\I18nMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 * @coversNothing
 */
class I18nMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::setContainer(new Container((new DefinitionSourceFactory(true))()));
        $config = new Config([
            StdoutLoggerInterface::class => [
                LogLevel::DEBUG,
            ],
            'translatable' => [
                'locales' => [
                    'en',
                    'zh' => [
                        'CN',
                        'TW',
                    ],
                ],
            ],
        ]);
        ApplicationContext::getContainer()
            ->set(ConfigInterface::class, $config);
        ApplicationContext::getContainer()->define(
            RequestIdGeneratorInterface::class,
            RequestIdGenerator::class
        );
    }

    public function testProcess(): void
    {
        $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
        $instance = ApplicationContext::getContainer()->get(I18nMiddleware::class);
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->allows('hasHeader')
            ->andReturn(false, true, true, true, true);
        $request->allows('getHeaderLine')
            ->andReturn('en', 'zh_CN', 'zh_TW', 'test');
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->allows('handle')->andReturn(\Mockery::mock(ResponseInterface::class));
        $instance->process($request, $handler);
        $this->assertSame($translator->getLocale(), 'zh_CN');
        $instance->process($request, $handler);
        $this->assertSame($translator->getLocale(), 'en');
        $instance->process($request, $handler);
        $this->assertSame($translator->getLocale(), 'zh_CN');
        $instance->process($request, $handler);
        $this->assertSame($translator->getLocale(), 'zh_TW');
    }
}
