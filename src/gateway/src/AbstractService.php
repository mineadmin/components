<?php

namespace Mine\Gateway;

class AbstractService
{
    /**
     * @var Http
     */
    protected Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * 授权登录
     * @param string $account
     * @param string $password
     * @return string
     */
    public function auth(string $account, string $password): string
    {
        // todo...
        return 'token';
    }
}