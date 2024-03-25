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

namespace Mine\HttpServer\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\TranslatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class I18nMiddleware implements MiddlewareInterface
{
    public const HTTP_HEADER_KEY = 'Accept-Language';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ConfigInterface $config,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request->hasHeader(self::HTTP_HEADER_KEY)) {
            return $handler->handle($request);
        }
        $acceptLanguage = $request->getHeaderLine(self::HTTP_HEADER_KEY);
        $locales = $this->getLocales();
        if (! in_array($acceptLanguage, $locales, true)) {
            return $handler->handle($request);
        }
        $this->translator->setLocale($acceptLanguage);
        return $handler->handle($request);
    }

    private function getLocales(): array
    {
        $appLocales = $this->config->get('translatable.locales', []);
        $locales = [];
        foreach ($appLocales as $i => $v) {
            if (is_int($i)) {
                $locales[] = $v;
            }
            if (is_string($i) && is_array($v)) {
                foreach ($v as $sub) {
                    $locales[] = $i . '_' . $sub;
                }
            }
        }
        return $locales;
    }
}
