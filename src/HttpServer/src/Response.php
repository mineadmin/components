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

namespace Mine\HttpServer;

use Hyperf\HttpServer\Response as HyperfResponse;

use function Hyperf\Config\config;

/**
 * @mixin HyperfResponse
 */
class Response
{
    public function success(?string $message = null, array|object $data = [], int $code = 200): \Psr\Http\Message\ResponseInterface
    {
        $format = [
            'requestId' => RequestIdHolder::getId(),
            'success' => true,
            'message' => 'success',
            'code' => $code,
            'data' => $data,
        ];
        $this->handleResponse();
        return $this->json($format);
    }

    public function error(string $message = '', int $code = 500, array $data = []): \Psr\Http\Message\ResponseInterface
    {
        $format = [
            'requestId' => RequestIdHolder::getId(),
            'success' => false,
            'code' => $code,
            'message' => 'fail',
        ];

        if (! empty($data)) {
            $format['data'] = &$data;
        }
        $this->handleResponse();
        return $this->json($format);
    }

    public function handleResponse(): void
    {
        $headers = config('mineadmin.http.headers', []);
        foreach ($headers as $key => $value) {
            $this->getresponse()->addHeader($key, $value);
        }
    }
}
