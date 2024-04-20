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

namespace Mine\Security\Http;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;
use Mine\Security\Http\Attribute\CurrentUser;

class RegisterCurrentUserPropertyHandler
{
    public static bool $registered = false;

    public static function register(): void
    {
        if (static::$registered) {
            return;
        }
        PropertyHandlerManager::register(CurrentUser::class, [static::class, 'handle']);
    }

    public static function handle($object, $currentClassName, $targetClassName, $property, $annotation): void
    {
        if ($annotation instanceof CurrentUser) {
            $reflectionProperty = ReflectionManager::reflectProperty($currentClassName, $property);
            $container = ApplicationContext::getContainer();
            $reflectionProperty->setValue(new CurrentUserProxy(
                $annotation->secret,
                $container,
            ));
        }
    }
}
