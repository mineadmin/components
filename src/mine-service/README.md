# Crud Service

## 提供了一套类似 SpringBoot 注解,以及类似 mybatis-plus CrudService的契约

## 现已实现注解

使用前需要use对应命名空间 `Mine\Annotation`

- `Component`
- `Defined`
- `DependProxy`
- `Override`
- `PostConstruct`
- `Service`

## 有部分情况在使用`Service`注解时会造成前后加载顺序不一致,可以在项目的`config/container.php`修改为以下

```php
<?php
/**
 * Initialize a dependency injection container that implemented PSR-11 and return the container.
 */

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Mine\Annotation\DependProxyCollector;

// https://github.com/kanyxmo/mine/pull/14
$container = new Container((new DefinitionSourceFactory())());
DependProxyCollector::walk([$container, 'define']);

if (! $container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
return ApplicationContext::setContainer($container);

```