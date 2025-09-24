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
use Hyperf\Context\RequestContext;
use Hyperf\Di\Container;
use Hyperf\HttpMessage\Server\Request;
use Mine\Support\Request as SupportRequest;
use Mine\Support\Request\ClientIpRequestConstant;
use Mine\Support\Request\ClientIpRequestTrait;

class ClientIpTest extends SupportRequest
{
    use ClientIpRequestTrait;
}

$getClientIpsProvider = [
    // simple IPv4
    [['88.88.88.88'], '88.88.88.88', null, null],
    // trust the IPv4 remote addr
    [['88.88.88.88'], '88.88.88.88', null, ['88.88.88.88']],

    // simple IPv6
    [['::1'], '::1', null, null],
    // trust the IPv6 remote addr
    [['::1'], '::1', null, ['::1']],

    // forwarded for with remote IPv4 addr not trusted
    [['127.0.0.1'], '127.0.0.1', '88.88.88.88', null],
    // forwarded for with remote IPv4 addr trusted + comma
    [['88.88.88.88'], '127.0.0.1', '88.88.88.88,', ['127.0.0.1']],
    // forwarded for with remote IPv4 and all FF addrs trusted
    [['88.88.88.88'], '127.0.0.1', '88.88.88.88', ['127.0.0.1', '88.88.88.88']],
    // forwarded for with remote IPv4 range trusted
    [['88.88.88.88'], '123.45.67.89', '88.88.88.88', ['123.45.67.0/24']],

    // forwarded for with remote IPv6 addr not trusted
    [['1620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3', null],
    // forwarded for with remote IPv6 addr trusted
    [['2620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3']],
    // forwarded for with remote IPv6 range trusted
    [['88.88.88.88'], '2a01:198:603:0:396e:4789:8e99:890f', '88.88.88.88', ['2a01:198:603:0::/65']],

    // multiple forwarded for with remote IPv4 addr trusted
    [['88.88.88.88', '87.65.43.21', '127.0.0.1'], '123.45.67.89', '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89']],
    // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted
    [['87.65.43.21', '127.0.0.1'], '123.45.67.89', '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '88.88.88.88']],
    // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted but in the middle
    [['88.88.88.88', '127.0.0.1'], '123.45.67.89', '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '87.65.43.21']],
    // multiple forwarded for with remote IPv4 addr and all reverse proxies trusted
    [['127.0.0.1'], '123.45.67.89', '127.0.0.1, 87.65.43.21, 88.88.88.88', ['123.45.67.89', '87.65.43.21', '88.88.88.88', '127.0.0.1']],

    // multiple forwarded for with remote IPv6 addr trusted
    [['2620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3']],
    // multiple forwarded for with remote IPv6 addr and some reverse proxies trusted
    [['3620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3', '2620:0:1cfe:face:b00c::3']],
    // multiple forwarded for with remote IPv4 addr and some reverse proxies trusted but in the middle
    [['2620:0:1cfe:face:b00c::3', '4620:0:1cfe:face:b00c::3'], '1620:0:1cfe:face:b00c::3', '4620:0:1cfe:face:b00c::3,3620:0:1cfe:face:b00c::3,2620:0:1cfe:face:b00c::3', ['1620:0:1cfe:face:b00c::3', '3620:0:1cfe:face:b00c::3']],

    // client IP with port
    [['88.88.88.88'], '127.0.0.1', '88.88.88.88:12345, 127.0.0.1', ['127.0.0.1']],

    // invalid forwarded IP is ignored
    [['88.88.88.88'], '127.0.0.1', 'unknown,88.88.88.88', ['127.0.0.1']],
    [['88.88.88.88'], '127.0.0.1', '}__test|O:21:&quot;JDatabaseDriverMysqli&quot;:3:{s:2,88.88.88.88', ['127.0.0.1']], ];

function getRequestInstanceForClientIpTests(string $remoteAddr, ?string $httpForwardedFor, ?array $trustedProxies)
{
    $swooleRequest = Mockery::mock(Swoole\Http\Request::class);
    $serverParams = [
        'remote_addr' => $remoteAddr,
    ];
    if ($httpForwardedFor !== null) {
        $serverParams['x-forwarded-for'] = $httpForwardedFor;
    }
    $swooleRequest->server = $serverParams;
    $swooleRequest->header = $serverParams;
    if ($trustedProxies !== null) {
        ClientIpTestRequest::setTrustedProxies($trustedProxies, ClientIpRequestConstant::HEADER_X_FORWARDED_FOR);
    }
    $swooleRequest->allows('rawContent')->andReturn('');
    $request = Request::loadFromSwooleRequest($swooleRequest);
    RequestContext::set($request);
    SupportRequest::resetTrustedRemoteAddr();
    return new ClientIpTestRequest(Mockery::mock(Container::class));
}

test('testGetClientIpsForwarded', static function ($expected, $remoteAddr, $httpForwardedFor, $trustedProxies) {
    $testRequest = getRequestInstanceForClientIpTests($remoteAddr, $httpForwardedFor, $trustedProxies);
    $result = $testRequest->getClientIps();
    expect($result)->toEqual($expected);
    Mockery::close();
})
    ->with($getClientIpsProvider);

function getRequestInstanceForClientIpsForwardedTests(string $remoteAddr, ?string $httpForwarded, ?array $trustedProxies)
{
    $swooleRequest = Mockery::mock(Swoole\Http\Request::class);
    $serverParams = [
        'remote_addr' => $remoteAddr,
    ];
    if ($httpForwarded !== null) {
        $serverParams['forwarded'] = $httpForwarded;
    }
    $swooleRequest->server = $serverParams;
    $swooleRequest->header = $serverParams;
    if ($trustedProxies !== null) {
        ClientIpTestRequest::setTrustedProxies($trustedProxies, ClientIpRequestConstant::HEADER_FORWARDED);
    }
    $swooleRequest->allows('rawContent')->andReturn('');
    $request = Request::loadFromSwooleRequest($swooleRequest);
    RequestContext::set($request);
    SupportRequest::resetTrustedRemoteAddr();
    return new ClientIpTestRequest(Mockery::mock(Container::class));
}

test('testGetClientIps', static function ($expected, $remoteAddr, $httpForwardedFor, $trustedProxies) {
    $request = getRequestInstanceForClientIpsForwardedTests($remoteAddr, $httpForwardedFor, $trustedProxies);
    $result = $request->getClientIps();
    expect($result)->toEqual($expected);
    Mockery::close();
})->with([
    [['127.0.0.1'], '127.0.0.1', 'for="_gazonk"', null],
    [['127.0.0.1'], '127.0.0.1', 'for="_gazonk"', ['127.0.0.1']],
    [['88.88.88.88'], '127.0.0.1', 'for="88.88.88.88:80"', ['127.0.0.1']],
    [['192.0.2.60'], '::1', 'for=192.0.2.60;proto=http;by=203.0.113.43', ['::1']],
    [['2620:0:1cfe:face:b00c::3', '192.0.2.43'], '::1', 'for=192.0.2.43, for="[2620:0:1cfe:face:b00c::3]"', ['::1']],
    [['2001:db8:cafe::17'], '::1', 'for="[2001:db8:cafe::17]:4711', ['::1']],
]);

test('demo', static function () {
    // Simple demonstration test
    expect(true)->toBeTrue();
});
