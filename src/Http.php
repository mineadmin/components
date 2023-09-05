<?php

namespace Mine\Gateway;

use Hyperf\Guzzle\ClientFactory;
use Hyperf\GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

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
     * @param string $uri
     * @param array $options
     * @param bool $getResponse
     * @return ResponseInterface|array|null
     */
    public function post(string $uri, array $options = [], bool $getResponse = false): null|ResponseInterface|array
    {
        return $this->responseHandler(
            $this->client->post($uri, array_merge(['Content-Type' => 'application/json'], $options)),
            $getResponse
        );
    }

    /**
     * get 请求
     * @param string $uri
     * @param array $options
     * @param bool $getResponse
     * @return ResponseInterface|array|null
     */
    public function get(string $uri, array $options = [], bool $getResponse = false): null|ResponseInterface|array
    {
        return $this->responseHandler(
            $this->client->get($uri, array_merge(['Content-Type' => 'application/json'], $options)),
            $getResponse
        );
    }

    /**
     * @param ResponseInterface $response
     * @param bool $getResponse
     * @return ResponseInterface|array|null
     */
    protected function responseHandler(ResponseInterface $response, bool $getResponse): null|ResponseInterface|array
    {
        if ($response->getStatusCode() === 200) {
            return $getResponse ? $response : $response->getBody();
        }
        return null;
    }
}