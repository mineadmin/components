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

namespace Mine\Crontab;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;

class CrontabUrl
{
    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {}

    public function getClient(): Client
    {
        return $this->clientFactory->create();
    }

    public function execute(string $url)
    {
        return $this->getClient()->get($url);
    }
}
