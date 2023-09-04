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
}