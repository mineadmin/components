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

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Pool\Connection as AbstractConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Connection extends AbstractConnection implements ConnectionInterface
{
    protected ConnectionFactory $factory;

    protected LoggerInterface $logger;

    protected ?\Doctrine\DBAL\Connection $connection = null;

    public function __construct(
        ContainerInterface $container,
        PoolInterface $pool,
        private readonly array $config
    ) {
        parent::__construct($container, $pool);
        $this->factory = $container->get(ConnectionFactory::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function __call(string $name, array $arguments)
    {
        if ($this->connection === null) {
            $this->getActiveConnection();
        }
        return $this->connection->{$name}(...$arguments);
    }

    public function reconnect(): bool
    {
        try {
            $this->close();
            $this->connection = $this->factory->make($this->config);
            $this->lastUseTime = microtime(true);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function close(): bool
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }

        return true;
    }

    /**
     * @throws ConnectionException
     */
    public function getActiveConnection(): ?\Doctrine\DBAL\Connection
    {
        if ($this->check()) {
            return $this->connection;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this->connection;
    }
}
