<?php

namespace Mine\HttpServer;

use Hyperf\HttpServer\Router\Dispatched;

class Request extends \Hyperf\HttpServer\Request
{
    public function ip(): string
    {
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
    }

    public function getAction(): string|null
    {
        /**
         * @var Dispatched $dispatch
         * @var \Hyperf\HttpServer\Request $this
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
    }
}