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

    private function merge(string $function): array
    {
        $commonFunc = 'common' . ucfirst($function);
        $actionFunc = $this->getAction() . ucfirst($function);
        return array_merge(
            $this->{$commonFunc}(),
            $this->{$actionFunc}()
        );
    }
}
