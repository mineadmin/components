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

namespace Mine\Support\Tests;

use Hyperf\Constants\ConstantsCollector;
use Mine\Support\ConstantUtils;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConstantUtilsTest extends TestCase
{
    protected function setUp(): void
    {
        // 在这里进行测试前的准备工作
    }

    protected function tearDown(): void
    {
        // 在这里进行测试后的清理工作
    }

    public function testGetConstantsBy()
    {
        // 测试用例1
        $enumClassName = 'Your\EnumClassName';
        $expectedResult = ['value1' => ['message' => 'Message 1'], 'value2' => ['message' => 'Message 2']];
        ConstantsCollector::set($enumClassName, $expectedResult);
        $result = ConstantUtils::getConstantsBy($enumClassName);
        $this->assertEquals($expectedResult, $result);

        // 测试用例2
        $enumClassName = 'Invalid\EnumClassName';
        $expectedResult = [];
        $result = ConstantUtils::getConstantsBy($enumClassName);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetLabelValue()
    {
        // 测试用例1
        $enumClassName = 'Your\EnumClassName';
        $expectedResult = [
            ['label' => 'Message 1', 'value' => 'value1'],
            ['label' => 'Message 2', 'value' => 'value2'],
        ];
        ConstantsCollector::set($enumClassName, ['value1' => ['message' => 'Message 1'], 'value2' => ['message' => 'Message 2']]);
        $result = ConstantUtils::getLabelValue($enumClassName);
        $this->assertEquals($expectedResult, $result);

        // 测试用例2
        $enumClassName = 'Invalid\EnumClassName';
        $expectedResult = [];
        $result = ConstantUtils::getLabelValue($enumClassName);
        $this->assertEquals($expectedResult, $result);
    }

    public function testCollectConvertLabelValue()
    {
        // 测试用例
        $collectData = [
            'value1' => ['message' => 'Message 1'],
            'value2' => ['message' => 'Message 2'],
        ];
        $expectedResult = [
            ['label' => 'Message 1', 'value' => 'value1'],
            ['label' => 'Message 2', 'value' => 'value2'],
        ];
        $result = ConstantUtils::collectConvertLabelValue($collectData);
        $this->assertEquals($expectedResult, $result);
    }

    public function testExceptConstantData()
    {
        // 测试用例
        $enumsClass = 'Your\EnumClassName';
        $values = ['value1'];
        $expectedResult = ['value2' => ['message' => 'Message 2']];
        ConstantsCollector::set($enumsClass, ['value1' => ['message' => 'Message 1'], 'value2' => ['message' => 'Message 2']]);
        $result = ConstantUtils::exceptConstantData($enumsClass, $values);
        $this->assertEquals($expectedResult, $result);
    }
}
