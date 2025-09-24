<?php

namespace Mine\Doctrine\Pool;

use Hyperf\Contract\ContainerInterface;
use Mine\Doctrine\Config;

final class PoolFactory
{
    /**
     * @var Pool[]
     */
    protected array $pools = [];

    public function __construct(
        private readonly Config $config,
        private readonly ContainerInterface $container
    ){}


    public function getPool(string $name): Pool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }
        $this->pools[$name] = new Pool($this->container,$this->config,$name);
        return $this->pools[$name];
    }
}