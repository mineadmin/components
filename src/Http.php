<?php

namespace Mine\Gateway;

use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use GuzzleHttp\Client;
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
     * @param string $uri
     * @param array $options
     * @param bool $getResponse
     * @return \Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public function post(string $uri, array $options = [], bool $getResponse = false): \Psr\Http\Message\StreamInterface
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
     * @return \Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public function get(string $uri, array $options = [], bool $getResponse = false): \Psr\Http\Message\StreamInterface
    {
        return $this->responseHandler(
            $this->client->get($uri, array_merge(['Content-Type' => 'application/json'], $options)),
            $getResponse
        );
    }

    /**
     * @param ResponseInterface $response
     * @param bool $getResponse
     * @return StreamInterface|null
     */
    protected function responseHandler(ResponseInterface $response, bool $getResponse): ?\Psr\Http\Message\StreamInterface
    {
        if ($response->getStatusCode() === 200) {
            return $getResponse ? $response : $response->getBody();
        }
        return null;
    }
}