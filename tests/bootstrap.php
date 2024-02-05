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
use Hyperf\Config\Listener\RegisterPropertyHandlerListener;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\PropertyHandlerVisitor;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Psr\Container\ContainerInterface;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require_once BASE_PATH . '/vendor/autoload.php';
$container = new Container((new DefinitionSourceFactory(true))());

if (! $container instanceof ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
ApplicationContext::setContainer($container);

ClassLoader::init();
// Register AST visitors to the collector.
AstVisitorRegistry::insert(PropertyHandlerVisitor::class);
AstVisitorRegistry::insert(ProxyCallVisitor::class);

// Register Property Handler.
RegisterInjectPropertyHandler::register();

(new RegisterPropertyHandlerListener())->process(new stdClass());
