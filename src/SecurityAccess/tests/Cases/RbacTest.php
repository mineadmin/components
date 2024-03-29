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
use Mine\Security\Access\Contract\Access;
use Mine\Security\Access\Rbac;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RbacTest extends TestCase
{
    private Rbac $rbac;

    private Enforcer $enforcer;

    protected function setUp(): void
    {
        $accessMock = \Mockery::mock(Access::class);
        $this->enforcer = \Mockery::mock(Enforcer::class);

        $accessMock->allows('get')
            ->with('rbac')
            ->andReturn($this->enforcer);

        $this->rbac = new Rbac($accessMock);
    }

    public function testCallMethod(): void
    {
        $methodName = 'testMethod';
        $arguments = ['arg1', 'arg2'];
        $this->enforcer->allows($methodName)->with(...$arguments)->andReturn('result');
        $res = $this->rbac->{$methodName}(...$arguments);
        $this->assertEquals('result', $res);
    }
}
