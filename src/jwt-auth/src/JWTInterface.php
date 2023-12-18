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

namespace Xmo\JWTAuth;

/**
 * Interface JWTInterface.
 */
interface JWTInterface
{
    public function setSceneConfig(string $scene = 'default', $value = null);

    public function getSceneConfig(string $scene = 'default');

    public function setScene(string $scene);

    public function getScene();
}
