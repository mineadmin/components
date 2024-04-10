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

namespace Mine\HttpServer;

use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Validation\Request\FormRequest as Base;

class FormRequest extends Base
{
    public function messages(): array
    {
        return $this->merge(__FUNCTION__);
    }

    public function attributes(): array
    {
        return $this->merge(__FUNCTION__);
    }

    public function rules(): array
    {
        return $this->merge(__FUNCTION__);
    }

    public function getAction(): ?string
    {
        /**
         * @var Dispatched $dispatch
         */
        $dispatch = $this->getAttribute(Dispatched::class);
        $callback = $dispatch?->handler?->callback;
        if (is_array($callback) && count($callback) === 2) {
            return $callback[1];
        }
        if (is_string($callback)) {
            if (str_contains($callback, '@')) {
                return explode('@', $callback)[1] ?? null;
            }
            if (str_contains($callback, '::')) {
                return explode('::', $callback)[1] ?? null;
            }
        }
        return null;
    }

    private function merge(string $function): array
    {
        $commonFunc = 'common' . ucfirst($function);
        $actionFunc = $this->getAction() . ucfirst($function);
        return array_merge(
            method_exists($this, $commonFunc) ? $this->{$commonFunc}() : [],
            method_exists($this, $actionFunc) ? $this->{$actionFunc}() : []
        );
    }
}
