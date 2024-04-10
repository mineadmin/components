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

namespace Mine\Admin\Bundle\Traits;

use Mine\CrudBundle\Abstracts\AbstractDao;
use Mine\CrudBundle\Abstracts\CrudDao;

trait ServiceTrait
{
    public AbstractDao|CrudDao $dao;

    public function lst(array $params, int $page, int $size): array
    {
        return $this->dao->page($params, $page, $size)->toArray();
    }

    public function save(array $params): array
    {
        return $this->dao->save($params)->toArray();
    }

    public function update(mixed $id, array $data, bool $isModel = true): bool
    {
        return $this->dao->update($id, $data, $isModel);
    }

    public function delete(array|int|string $ids): bool
    {
        if (! is_array($ids)) {
            $this->dao->remove($ids);
        }
        foreach ($ids as $id) {
            $this->dao->remove($id);
        }
        return true;
    }

    public function realDelete(array|int|string $ids): bool
    {
        return $this->dao->delete($ids);
    }

    public function recycle(array $params, int $page, int $size): array
    {
        $params['recycle'] = true;
        return $this->lst($params, $page, $size);
    }

    public function changeStatus(mixed $id, string $value, string $filed = 'status'): bool
    {
        return $this->update($id, [
            $filed => $value,
        ]);
    }
}
