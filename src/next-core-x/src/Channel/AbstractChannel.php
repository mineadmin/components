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

namespace Mine\NextCoreX\Channel;

use Hyperf\Context\ApplicationContext;
use Mine\NextCoreX\Interfaces\Channel;
use Mine\NextCoreX\Interfaces\Serialize;
use Mine\NextCoreX\Protocols\PhpSerialize;
use Mine\NextCoreX\ReadConfig;

abstract class AbstractChannel implements Channel
{
    protected string $configPrefix;

    public function __construct(
        private readonly ReadConfig $config
    ) {}

    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get($this->getConfigPrefix() . '.' . $key, $default);
    }

    protected function getConfigPrefix(): string
    {
        return $this->configPrefix;
    }

    protected function getSerialize(): Serialize
    {
        return ApplicationContext::getContainer()->make($this->config->get('serialize', PhpSerialize::class));
    }
}
