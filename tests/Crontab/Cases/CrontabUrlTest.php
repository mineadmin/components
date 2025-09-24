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
use GuzzleHttp\Client;
use Hyperf\Context\ApplicationContext;
use Hyperf\Guzzle\ClientFactory;
use Mine\Crontab\CrontabUrl;
use Psr\Http\Message\ResponseInterface;

test('execute', static function () {
    $clientFactory = Mockery::mock(ClientFactory::class);
    $client = Mockery::mock(Client::class);
    $client->allows('get')->andReturnUsing(static function ($url) {
        expect($url)->toBe('http://mineadmin.com');
        return Mockery::mock(ResponseInterface::class);
    });
    $clientFactory->allows('create')->andReturn($client);
    ApplicationContext::getContainer()->set(ClientFactory::class, $clientFactory);
    $crontabUrl = ApplicationContext::getContainer()->get(CrontabUrl::class);
    $crontabUrl->execute('http://mineadmin.com');
});
