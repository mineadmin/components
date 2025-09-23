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

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Hyperf\Cache\CacheManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Pool\Event\ReleaseConnection;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

use function Hyperf\Support\env;

final readonly class EntityManagerFactory
{
    public function __construct(
        private ConfigInterface $config,
        private CacheManager $cacheManager
    ) {}

    public function create(string $poolName = 'default'): EntityManager
    {
        [$paths,$isDevMode,$cache,$proxyDir,$database] = array_values($this->getConfig());

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: $isDevMode,
            proxyDir: $proxyDir,
            cache: $this->createCacheDriver($cache)
        );
        $params = $database[$poolName] ?? throw new \InvalidArgumentException("Database pool [{$poolName}] not found");
        $params['wrapperClass'] = ConnectionPool::class;
        $connection = DriverManager::getConnection($params, $config);
        return new EntityManager($connection, $config);
    }

    private function getConfig(): array
    {
        return array_merge([
            'paths' => [
                BASE_PATH . '/app/Entity',
            ],
            'isDevMode' => (bool) env('APP_DEBUG'),
            'cache' => 'default',
            'proxy_dir' => BASE_PATH . '/runtime/container/doctrine/proxies',
            'database' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => BASE_PATH . '/db.sqlite',
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
        ], $this->config->get('doctrine', []));
    }

    private function createCacheDriver(mixed $cache): Psr16Adapter
    {
        return new Psr16Adapter($this->cacheManager->getDriver($cache), 'doctrine_');
    }
}
