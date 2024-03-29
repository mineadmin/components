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

use Mine\Security\Access\Rbac;
use Mine\Security\Access\RbacFacade;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RbacFacadeTest extends TestCase
{
    public function testGetFacadeRoot(): void
    {
        $this->assertEquals(Rbac::class, RbacFacade::getFacadeRoot());
    }
}
