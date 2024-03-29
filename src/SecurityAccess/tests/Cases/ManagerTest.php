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

namespace Mine\Security\Access\Tests\Cases;

use Casbin\Enforcer;
use Hyperf\Config\Config;
use Mine\Security\Access\Exception\AccessException;
use Mine\Security\Access\Manager;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ManagerTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        // Set up necessary dependencies and configurations for the Manager class
        // For example, create a mock Config object with the required method and return values
        $this->config = \Mockery::mock(Config::class);
        $this->manager = new Manager($this->config);
    }

    public function testGetWithoutName(): void
    {
        // Set up the expected default value from the config
        $expectedDefault = 'rbac';
        $this->config->allows('get')
            ->with('access.default', null)
            ->andReturn($expectedDefault);
        $this->config->allows('get')
            ->with('access.component.rbac', null)
            ->andReturn([
                'construct' => [
                    dirname(__DIR__, 2) . '/publish/rbac_model.conf',
                    dirname(__DIR__, 2) . '/publish/rbac_policy.csv',
                ],
                'enforcer' => Enforcer::class,
            ]);

        // Call the get method without passing a name
        $enforcer = $this->manager->get();

        // Assert that the getAdapter method is called with the expected default value
        $this->assertInstanceOf(Enforcer::class, $enforcer);
    }

    public function testGetWithName(): void
    {
        // Set up the expected adapter name and config
        $adapterName = 'customAdapter';
        $adapterConfig = [
            'construct' => [
                dirname(__DIR__, 2) . '/publish/rbac_model.conf',
                dirname(__DIR__, 2) . '/publish/rbac_policy.csv',
            ],
            'enforcer' => Enforcer::class,
        ];
        $this->config->allows('get')
            ->with('access.component.' . $adapterName, null)
            ->andReturn($adapterConfig);

        // Call the get method with the adapter name
        $enforcer = $this->manager->get($adapterName);

        // Assert that the getAdapter method is called with the expected adapter name
        $this->assertInstanceOf(Enforcer::class, $enforcer);
    }

    public function testGetAdapterWithNonExistentAdapter(): void
    {
        // Set up the expected adapter name and return null for the config
        $adapterName = 'nonExistentAdapter';
        $this->config->allows('get')
            ->with('access.component.' . $adapterName, null)
            ->andReturn(null);

        // Assert that an AccessException is thrown when the adapter does not exist
        $this->expectException(AccessException::class);
        $this->manager->get($adapterName);
    }

    public function testGetAdapterWithMissingConstructOrEnforcer(): void
    {
        // Set up the expected adapter name and incomplete config
        $adapterName = 'incompleteAdapter';
        $adapterConfig = [
            'construct' => [],
            'enforcer' => 'IncompleteEnforcerClass',
        ];
        $this->config->allows('get')
            ->with('access.component.' . $adapterName, null)
            ->andReturn($adapterConfig);

        // Assert that an AccessException is thrown when the adapter construct or enforcer is missing
        $this->expectException(AccessException::class);
        $this->manager->get($adapterName);
    }
}
