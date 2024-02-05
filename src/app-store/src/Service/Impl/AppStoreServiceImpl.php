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

namespace Xmo\AppStore\Service\Impl;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Xmo\AppStore\Service\AppStoreService;

use function Hyperf\Support\env;
use function Hyperf\Translation\trans;

final class AppStoreServiceImpl implements AppStoreService
{
    private readonly string $appStoreServer;

    private readonly Client $client;

    public function __construct(
        ClientFactory $clientFactory
    ) {
        $this->appStoreServer = 'https://www.mineadmin.com/server/store/';
        $this->client = $clientFactory->create([
            'base_uri' => $this->appStoreServer,
            'timeout' => 10.0,
        ]);
    }

    public function request(string $uri, array $data = [])
    {
        $response = $this->client->post($uri, [
            'json' => $data,
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(trans('app-store.store.response_fail'));
        }
        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return $response;
    }

    private function getAccessToken(): string
    {
        $accessToken = env('MINE_ACCESS_TOKEN');
        if (empty($accessToken)) {
            throw new \RuntimeException(trans('app-store.access_token_null'));
        }
        return (string) $accessToken;
    }
}
