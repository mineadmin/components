<?php

namespace Mine\Doctrine\Pool;

use Doctrine\DBAL\DriverManager;
use Hyperf\Collection\Arr;
use Hyperf\Support\SafeCaller;

final readonly class ConnectionFactory
{
    public function __construct(
        private SafeCaller $safeCaller
    ){}


    public function make(array $config): \Doctrine\DBAL\Connection
    {
        $processedConfig = $this->parser($config);
        return DriverManager::getConnection($processedConfig);
    }

    private function parser(array $config): array
    {
        if (Arr::has($config,'wrapper')) {
            $wrapper = Arr::get($config,'wrapper');
            // Remove wrapper from config before processing
            unset($config['wrapper']);
            // Call wrapper to process configuration
            $this->safeCaller->call(fn() => $wrapper($config));
        }
        return $config;
    }
}