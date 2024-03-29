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

namespace Mine\Security\Http\Tests\Cases;

use Mine\Security\Http\TokenObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TokenObjectTest extends TestCase
{
    public function testTokenObject()
    {
        $this->assertTrue(true);
        $instance = new TokenObject();
        $this->assertInstanceOf(TokenObject::class, $instance);
        $instance->setIssuedBy('xxx');
        $this->assertEquals('xxx', $instance->getIssuedBy());
        $instance->setClaims(['xxxx']);
        $this->assertEquals(['xxxx'], $instance->getClaims());
        $instance->setHeaders(['xxxx']);
        $this->assertEquals(['xxxx'], $instance->getHeaders());
    }
}
