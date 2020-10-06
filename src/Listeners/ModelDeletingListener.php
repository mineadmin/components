<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/translatable.
 *
 * @link     https://github.com/hyperf-ext/translatable
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/translatable/blob/master/LICENSE
 */
namespace HyperfExt\Translatable\Listeners;

use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Event\Contract\ListenerInterface;
use HyperfExt\Translatable\Contracts\TranslatableInterface;

class ModelDeletingListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Deleting::class,
        ];
    }

    public function process(object $event)
    {
        if (($model = $event->getModel()) instanceof TranslatableInterface && $model->isDeleteTranslationsCascade() === true) {
            $model->deleteTranslations();
        }
    }
}
