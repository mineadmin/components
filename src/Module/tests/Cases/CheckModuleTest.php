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

namespace Mine\Module\Tests\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mine\Module\CheckModule;
use Mine\Module\Exception\ModuleConfigException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CheckModuleTest extends TestCase
{
    protected function setUp(): void
    {
        ApplicationContext::getContainer()
            ->set(ConfigInterface::class, new Config([]));
    }

    public function testCheck(): void
    {
        $checkModule = ApplicationContext::getContainer()->get(CheckModule::class);
        $modules = [
            'test' => [
                'name' => 'test',
            ],
            'test2' => [
                'name' => 'xxx',
                'label' => 'xxx',
                'description' => 'xxx',
                'installed' => true,
                'enable' => true,
                'version' => '1.0.0',
                'order' => 1,
            ],
        ];
        try {
            $checkModule->check('test', $modules['test']);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ModuleConfigException::class, $e);
        }
        $this->assertTrue($checkModule->check('test2', $modules['test2']));
    }
}
