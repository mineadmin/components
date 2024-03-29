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

namespace Mine\SecurityBundle\Tests;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\ContextInterface;
use Mine\SecurityBundle\Contract\TokenInterface;
use Mine\SecurityBundle\Contract\UserProviderInterface;
use Mine\SecurityBundle\Security;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SecurityTest extends TestCase
{
    public function testConstruct()
    {
        $config = \Mockery::mock(Config::class);
        $container = \Mockery::mock(ContainerInterface::class);
        $this->assertInstanceOf(Security::class, new Security($config, ApplicationContext::getContainer()));
    }

    public function testGet()
    {
        $config = \Mockery::mock(Config::class);
        $config->allows('get')->andReturn('xxx');
        $container = \Mockery::mock(ContainerInterface::class);
        $security = new Security($config, $container);
        $container->allows('get')
            ->andReturn(\Mockery::mock(TokenInterface::class), \Mockery::mock(UserProviderInterface::class), \Mockery::mock(ContextInterface::class));
        $this->assertInstanceOf(TokenInterface::class, $security->getToken());
        $this->assertInstanceOf(UserProviderInterface::class, $security->getUserProvider());
        $this->assertInstanceOf(ContextInterface::class, $security->getContext());
    }
}
