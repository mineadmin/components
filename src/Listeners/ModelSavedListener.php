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

use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Contract\ListenerInterface;
use HyperfExt\Translatable\Contracts\TranslatableInterface;

class ModelSavedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Saved::class,
        ];
    }

    public function process(object $event)
    {
        if (($model = $event->getModel()) instanceof TranslatableInterface) {
            $model->saveTranslations();
        }
    }
}
