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

use Hyperf\Context\ApplicationContext;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Hyperf\HttpServer\Router\Dispatched;
use Mine\HttpServer\Response as MineResponse;

class BootApplicationListener implements ListenerInterface
{
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
        ApplicationContext::getContainer()->set(Response::class, MineResponse::class);
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

        Request::macro('getAction', function () {
            /**
             * @var Dispatched $dispatch
             * @var Request $this
             */
            $dispatch = $this->getAttribute(Dispatched::class);
            $callback = $dispatch?->handler?->callback;
            if (is_array($callback) && count($callback) === 2) {
                return $callback[1];
            }
            if (is_string($callback)) {
                if (str_contains($callback, '@')) {
                    return explode('@', $callback)[1] ?? null;
                }
                if (str_contains($callback, '::')) {
                    return explode('::', $callback)[1] ?? null;
                }
            }
            return null;
        });
    }
}
