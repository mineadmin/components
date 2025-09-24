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
use Hyperf\DbConnection\Frequency;
use Hyperf\Pool\Pool as AbstractPool;
use Mine\Doctrine\Config;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class Pool extends AbstractPool
{
    private array $options = [];

    private array $config = [];

    public function __construct(
        ContainerInterface $container,
        Config $config,
        private readonly string $name
    ) {
        $key = \sprintf('database.%s', $name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException("Database pool [{$name}] not found");
        }

        $config->set($key . '.name', $name);
        $this->options = $config->get($key . '.option', []);
        $this->config = $config->get($key);

        $this->frequency = make(Frequency::class, [$this]);

        parent::__construct($container, $this->options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->config);
    }
}
