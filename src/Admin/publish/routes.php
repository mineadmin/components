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
use Hyperf\HttpServer\Router\Router;
use Mine\Admin\Bundle\Controller\PassportController;

Router::addServer('http', static function () {
    Router::addGroup('system', static function () {
        // 登录
        Router::post('login', [PassportController::class, 'login']);
    });
});
