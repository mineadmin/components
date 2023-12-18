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

namespace Mine\Gateway;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Http
{
    protected Client $client;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create([
            'base_uri' => 'http://192.168.3.86:9601',
        ]);
    }

    /**
     * post 请求
     * @throws GuzzleException
     */
    public function post(string $uri, array $options = [], bool $getResponse = false): StreamInterface
    {
        return $this->responseHandler(
            $this->client->post($uri, array_merge(['Content-Type' => 'application/json'], $options)),
            $getResponse
        );
    }

    /**
     * get 请求
     * @throws GuzzleException
     */
    public function get(string $uri, array $options = [], bool $getResponse = false): StreamInterface
    {
        return $this->responseHandler(
            $this->client->get($uri, array_merge(['Content-Type' => 'application/json'], $options)),
            $getResponse
        );
    }

    protected function responseHandler(ResponseInterface $response, bool $getResponse): ?StreamInterface
    {
        if ($response->getStatusCode() === 200) {
            return $getResponse ? $response : $response->getBody();
        }
        return null;
    }
}
