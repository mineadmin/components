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

namespace Mine\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;
use Hyperf\Cache\CacheManager;
use Hyperf\Pool\Event\ReleaseConnection;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

use function Hyperf\Support\env;

final class ORMSetupFactory
{
    public function __construct(
        private readonly Config $config,
        private readonly CacheManager $cacheManager
    ) {}

    public function make(): Configuration
    {
        [$paths,$isDevMode,$cache,$proxyDir] = array_values($this->getConfig());
        return ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: $isDevMode,
            proxyDir: $proxyDir,
            cache: $this->createCacheDriver($cache)
        );
    }

    private function getConfig(): array
    {
        $basePath = \defined('BASE_PATH') ? BASE_PATH : getcwd();
        return array_merge([
            'paths' => [
                $basePath . '/app/Entity',
            ],
            'isDevMode' => (bool) env('APP_DEBUG', true),
            'cache' => 'default',
            'proxy_dir' => $basePath . '/runtime/container/doctrine/proxies',
            'database' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => $basePath . '/db.sqlite',
                    'option' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'maxIdleTime' => 60,
                        'events' => [
                            ReleaseConnection::class,
                        ],
                    ],
                ],
            ],
        ], $this->config->get());
    }

    private function createCacheDriver(mixed $cache): Psr16Adapter
    {
        return new Psr16Adapter($this->cacheManager->getDriver($cache), 'doctrine_');
    }
}
