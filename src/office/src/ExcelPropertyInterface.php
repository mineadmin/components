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

interface ExcelPropertyInterface
{
    public function import(\Mine\MineModel $model, ?\Closure $closure = null): bool;

    public function export(string $filename, array|\Closure $closure): \Psr\Http\Message\ResponseInterface;
}
