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

namespace Mine\NextCoreX;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Mine\NextCoreX\Interfaces\Channel;

use function Hyperf\Support\call;

/**
 * @method static push(string $queue, mixed $data) Push a message to the specified queue
 * @method static pull(string $queue) Pulls a message to the specified queue
 * @method static publish(string $queue, mixed $data) Post a message to the specified queue
 * @method static subscribe(string $queue, callable $callback) Listens for messages from the specified queue and blocks the current thread.
 */
class Queue
{
    public function __construct(
        private readonly ReadConfig $config,
        private readonly ContainerInterface $container
    ) {}

    public function __call($name, $arguments)
    {
        return call([$this->getDriver(), $name], $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $driver = $container->get($container->get(ReadConfig::class)->get('driver'));
        return call([$driver, $name], $arguments);
    }

    public function getDriver(): Channel
    {
        return $this->container->get($this->config->get('driver'));
    }
}
