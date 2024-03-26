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

namespace Mine\Module;

use Hyperf\Validation\ValidatorFactory;
use Mine\Module\Exception\ModuleConfigException;

class CheckModule
{
    private array $rules = [
        'name' => 'required|string',
        'label' => 'required|string',
        'description' => 'required|string',
        'installed' => 'required|bool',
        'enable' => 'required|bool',
        'version' => 'required|string',
        'order' => 'required|integer',
    ];

    public function __construct(
        private readonly ValidatorFactory $validatorFactory
    ) {}

    public function check(string $module, array $config): bool
    {
        $validator = $this->validatorFactory->make($config, $this->rules);
        if ($validator->fails()) {
            throw new ModuleConfigException(sprintf('Module %s config is invalid. %s', $module, $validator->errors()->first()), 1);
        }
        return true;
    }
}
