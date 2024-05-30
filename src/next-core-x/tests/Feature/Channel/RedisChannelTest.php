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
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use Mine\NextCoreX\Channel\RedisChannel;
use Mine\NextCoreX\Protocols\PhpSerialize;
use Mine\NextCoreX\ReadConfig;

use function Hyperf\Support\env;

beforeEach(function () {
    $configInterface = new Config([
        'next-core-x' => [
            'redis' => [
                'channel' => 'queue:%s',
            ],
        ],
        'serialize' => PhpSerialize::class,
        'redis' => [
            'default' => [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'auth' => env('REDIS_AUTH', null),
                'port' => (int) env('REDIS_PORT', 6379),
                'db' => (int) env('REDIS_DB', 0),
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
                ],
            ],
        ],
    ]);
    $this->config = new ReadConfig($configInterface);
    ApplicationContext::getContainer()->set(ConfigInterface::class, $configInterface);
    $redis = ApplicationContext::getContainer()->get(Redis::class);
    $this->channel = new RedisChannel($this->config, $redis);
});

test('redis push and pull', function () {
    $payload = [
        'id' => 1,
        'event' => 'xxx',
    ];
    $this->channel->push('test', $payload);
    expect($this->channel->pull('test'))->toEqual($payload);
})->skip(version_compare(swoole_version(), '6.0', '>='), 'Skip for swoole 6.0+');
