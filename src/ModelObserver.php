<?php

declare(strict_types=1);
/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/xmo/MineAdmin
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
