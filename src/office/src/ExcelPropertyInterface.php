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

namespace Mine\Office;

use Mine\MineModel;
use Psr\Http\Message\ResponseInterface;

interface ExcelPropertyInterface
{
    public function import(MineModel $model, ?\Closure $closure = null): mixed;

    public function export(string $filename, array|\Closure $closure): ResponseInterface;
}
