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

namespace Mine\Module\Middleware;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Stringable\Str;
use Mine\HttpServer\Exception\BusinessException;
use Mine\Module\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckoutModuleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        if ($uri->getPath() === '/favicon.ico') {
            return $handler->handle($request);
        }

        if (mb_substr_count($uri->getPath(), '/') > 1) {
            [$empty, $moduleName, $controllerName] = explode('/', $uri->getPath());

            $path = $moduleName . '/' . $controllerName;

            $moduleName = Str::lower($moduleName);

            $module['enabled'] = false;

            foreach (Module::list() as $name => $item) {
                if (Str::lower($name) === $moduleName) {
                    $module = $item;
                    break;
                }
            }

            $annotation = AnnotationCollector::getClassesByAnnotation('Hyperf\HttpServer\Annotation\Controller');

            foreach ($annotation as $item) {
                if ($item->server === 'http' && $item->prefix === $path && ! $module['enabled']) {
                    throw new BusinessException('模块被禁用', 500);
                }
            }
        }

        return $handler->handle($request);
    }
}
