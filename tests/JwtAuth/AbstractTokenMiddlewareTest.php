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

namespace Mine\Tests\JwtAuth;

use Hyperf\Contract\ConfigInterface;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Jwt\Factory;
use Mine\Jwt\JwtInterface;
use Mine\JwtAuth\Interfaces\CheckTokenInterface;
use Mine\JwtAuth\Middleware\AbstractTokenMiddleware;
use Mine\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

/**
 * @internal
 */
#[CoversClass(AbstractTokenMiddleware::class)]
final class AbstractTokenMiddlewareTest extends TestCase
{
    public function testBasicFunctionalityWithValidInput(): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn(true);
        $request->allows('getHeaderLine')->with('Authorization')->andReturn('Bearer token');
        $request->allows('setAttribute')->with('token', \Mockery::any())->andReturn($request);
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        // Configure handler mock
        $expectedResponse = \Mockery::mock(PsrResponseInterface::class);
        $handler->allows('handle')->andReturn($expectedResponse);
        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);
        $checkToken->allows('checkJwt')->once();

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            // Implement any abstract methods if needed
            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->andReturn(\Mockery::mock(UnencryptedToken::class));
                return $jwt;
            }
        };

        // Execute the middleware
        $actualResponse = $middleware->process($request, $handler);

        // Assertions
        self::assertSame($expectedResponse, $actualResponse);
    }

    public function testGetTokenFromTokenHeader(): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn(false);
        $request->allows('hasHeader')->with('token')->andReturn(true);
        $request->allows('getHeaderLine')->with('token')->andReturn('my-token-value');
        $request->allows('setAttribute')->with('token', \Mockery::any())->andReturn($request);
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        // Configure handler mock
        $expectedResponse = \Mockery::mock(PsrResponseInterface::class);
        $handler->allows('handle')->andReturn($expectedResponse);
        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);
        $checkToken->allows('checkJwt')->once();

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->with('my-token-value')->andReturn(\Mockery::mock(UnencryptedToken::class));
                return $jwt;
            }
        };

        // Execute the middleware
        $actualResponse = $middleware->process($request, $handler);

        // Assertions
        self::assertSame($expectedResponse, $actualResponse);
    }

    public function testGetTokenFromQueryParams(): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn(false);
        $request->allows('hasHeader')->with('token')->andReturn(false);
        $request->allows('getQueryParams')->andReturn(['token' => 'query-token-value']);
        $request->allows('setAttribute')->with('token', \Mockery::any())->andReturn($request);
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        // Configure handler mock
        $expectedResponse = \Mockery::mock(PsrResponseInterface::class);
        $handler->allows('handle')->andReturn($expectedResponse);
        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);
        $checkToken->allows('checkJwt')->once();

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->with('query-token-value')->andReturn(\Mockery::mock(UnencryptedToken::class));
                return $jwt;
            }
        };

        // Execute the middleware
        $actualResponse = $middleware->process($request, $handler);

        // Assertions
        self::assertSame($expectedResponse, $actualResponse);
    }

    public function testGetTokenWithNoToken(): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn(false);
        $request->allows('hasHeader')->with('token')->andReturn(false);
        $request->allows('getQueryParams')->andReturn([]);
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->with('')->andThrow(new \RuntimeException('Empty token'));
                return $jwt;
            }
        };

        // Expect exception when no token is provided
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty token');

        // Execute the middleware
        $middleware->process($request, $handler);
    }

    public function testCheckTokenFailure(): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn(true);
        $request->allows('getHeaderLine')->with('Authorization')->andReturn('Bearer invalid-token');
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);

        // Mock token validation failure - use Mockery::any() to match any token object
        $checkToken->allows('checkJwt')->withAnyArgs()->andThrow(new \RuntimeException('Token validation failed'));

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->andReturn(\Mockery::mock(UnencryptedToken::class));
                return $jwt;
            }
        };

        // Expect exception when token validation fails
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token validation failed');

        // Execute the middleware
        $middleware->process($request, $handler);
    }

    #[DataProvider('provideTokenSourcePriorityCases')]
    public function testTokenSourcePriority(bool $hasAuthHeader, bool $hasTokenHeader, bool $hasQueryParam, string $expectedToken): void
    {
        // Mock dependencies
        $request = \Mockery::mock(ServerRequestPlusInterface::class);
        $request->allows('hasHeader')->with('Authorization')->andReturn($hasAuthHeader);
        $request->allows('hasHeader')->with('token')->andReturn($hasTokenHeader);

        if ($hasAuthHeader) {
            $request->allows('getHeaderLine')->with('Authorization')->andReturn('Bearer auth-token');
        }

        if ($hasTokenHeader) {
            $request->allows('getHeaderLine')->with('token')->andReturn('header-token');
        }

        $queryParams = $hasQueryParam ? ['token' => 'query-token'] : [];
        $request->allows('getQueryParams')->andReturn($queryParams);

        $request->allows('setAttribute')->with('token', \Mockery::any())->andReturn($request);
        $handler = \Mockery::mock(RequestHandlerInterface::class);

        // Configure handler mock
        $expectedResponse = \Mockery::mock(PsrResponseInterface::class);
        $handler->allows('handle')->andReturn($expectedResponse);
        $config = \Mockery::mock(ConfigInterface::class);
        $factory = new Factory($config);
        $checkToken = \Mockery::mock(CheckTokenInterface::class);
        $checkToken->allows('checkJwt')->once();

        // Create concrete implementation of abstract class for testing
        $middleware = new class($factory, $checkToken) extends AbstractTokenMiddleware {
            private string $receivedToken = '';

            public function getJwt(): JwtInterface
            {
                $jwt = \Mockery::mock(JwtInterface::class);
                $jwt->allows('parserAccessToken')->andReturnUsing(function ($token) {
                    $this->receivedToken = $token;
                    return \Mockery::mock(UnencryptedToken::class);
                });
                return $jwt;
            }

            public function getReceivedToken(): string
            {
                return $this->receivedToken;
            }
        };

        // Execute the middleware
        $middleware->process($request, $handler);

        // Assert that the correct token was used based on priority
        self::assertSame($expectedToken, $middleware->getReceivedToken());
    }

    public static function provideTokenSourcePriorityCases(): iterable
    {
        yield 'Authorization header has highest priority' => [true, true, true, 'auth-token'];
        yield 'Token header has second priority' => [false, true, true, 'header-token'];
        yield 'Query param has lowest priority' => [false, false, true, 'query-token'];
        yield 'Only Authorization header present' => [true, false, false, 'auth-token'];
        yield 'Only Token header present' => [false, true, false, 'header-token'];
        yield 'Only Query param present' => [false, false, true, 'query-token'];
    }
}
