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

namespace Mine\HttpServer\Log;

use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Snowflake\IdGeneratorInterface;
use Mine\HttpServer\Contract\Log\RequestIdGeneratorInterface;
use Ramsey\Uuid\Uuid;

class RequestIdGenerator implements RequestIdGeneratorInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function generate(): string
    {
        return Context::getOrSet(self::REQUEST_ID, function () {
            if (! class_exists(IdGeneratorInterface::class)) {
                return Uuid::uuid4()->toString();
            }
            return (string) $this->container->get(IdGeneratorInterface::class)->generate();
        });
    }
}
