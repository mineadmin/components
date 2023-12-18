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

namespace Mine\Translatable;

use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\Saved;

class ModelObserver
{
    public function saved(Saved $event)
    {
        $event->getModel()->saveTranslations();
    }

    public function deleting(Deleting $event)
    {
        if (($model = $event->getModel())->isDeleteTranslationsCascade() === true) {
            $model->deleteTranslations();
        }
    }
}
