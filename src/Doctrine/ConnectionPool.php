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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Pool\SimplePool\PoolFactory;

class ConnectionPool extends Connection
{
    public function __construct(
        #[\SensitiveParameter]
        protected array $_params,
        Driver $driver,
        ?Configuration $config = null
    ) {
        parent::__construct($_params, $driver, $config);
    }

    protected function connect(): DriverConnection
    {
        if ($this->_conn !== null) {
            return $this->_conn;
        }

        try {
            $this->_conn = $this->createPool();
        } catch (Driver\Exception $e) {
            throw $this->convertException($e);
        }

        return parent::connect();
    }

    private function createPool()
    {
        $poolFactory = ApplicationContext::getContainer()
            ->get(PoolFactory::class);
        $uniqueId = md5(json_encode($this->_params));
        return $poolFactory->get($uniqueId, function () {
            return $this->driver->connect($this->_params);
        }, $this->_params['option'] ?? [])
            ->get()
            ->getConnection();
    }
}
