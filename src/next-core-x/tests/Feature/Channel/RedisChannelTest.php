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
use Hyperf\Redis\Redis;
use Mine\NextCoreX\Channel\RedisChannel;
use Mine\NextCoreX\Protocols\PhpSerialize;
use Mine\NextCoreX\ReadConfig;

beforeEach(function () {
    $configInterface = new Config([
        'next-core-x' => [
            'redis' => [
                'channel' => 'queue:%s',
            ],
        ],
        'serialize' => PhpSerialize::class,
    ]);
    $this->config = new ReadConfig($configInterface);
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
});
