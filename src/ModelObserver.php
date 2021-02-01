<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/translatable.
 *
 * @link     https://github.com/hyperf-ext/translatable
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/translatable/blob/master/LICENSE
 */
namespace HyperfExt\Translatable;

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
