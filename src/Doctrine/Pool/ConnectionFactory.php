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

namespace Mine\Doctrine\Pool;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Hyperf\Collection\Arr;
use Hyperf\Support\SafeCaller;

final readonly class ConnectionFactory
{
    public function __construct(
        private SafeCaller $safeCaller
    ) {}

    public function make(array $config): Connection
    {
        $processedConfig = $this->parser($config);
        return DriverManager::getConnection($processedConfig);
    }

    private function parser(array $config): array
    {
        if (Arr::has($config, 'wrapper')) {
            $wrapper = Arr::get($config, 'wrapper');
            // Remove wrapper from config before processing
            unset($config['wrapper']);
            // Call wrapper to process configuration
            $this->safeCaller->call(static fn () => $wrapper($config));
        }
        return $config;
    }
}
