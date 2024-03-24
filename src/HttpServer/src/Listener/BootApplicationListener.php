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

namespace Mine\HttpServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Mine\HttpServer\Response as MineResponse;

class BootApplicationListener implements ListenerInterface
{
    public function __construct(
        private readonly MineResponse $response
    ) {}

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->registerRequestMacro();
        $this->registerResponseMacro();
    }

    private function registerResponseMacro(): void
    {
        Response::mixin($this->response);
    }

    private function registerRequestMacro(): void
    {
        Request::macro('ip', function () {
            /**
             * @var Request $this
             */
            $ip = $this->getServerParams()['remote_addr'] ?? '0.0.0.0';
            $headers = $this->getHeaders();
            if (isset($headers['x-real-ip'])) {
                $ip = $headers['x-real-ip'][0];
            } elseif (isset($headers['x-forwarded-for'])) {
                $ip = $headers['x-forwarded-for'][0];
            } elseif (isset($headers['http_x_forwarded_for'])) {
                $ip = $headers['http_x_forwarded_for'][0];
            } elseif (isset($headers['remote_host'])) {
                $ip = $headers['remote_host'][0];
            }
            return $ip;
        });
    }
}
