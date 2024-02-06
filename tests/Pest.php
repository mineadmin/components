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
uses(Mine\Tests\TestCase::class)
    ->beforeEach(function (){
        $mockConfig = Mockery::mock(\Hyperf\Contract\ConfigInterface::class);
        $mockConfig->allows('has')->andReturn(true);
        $mockConfig->allows('get')->andReturn([]);
        $mockConfig->allows('set')->andReturn(true);
        \Hyperf\Context\ApplicationContext::getContainer()
            ->set(\Hyperf\Contract\ConfigInterface::class,$mockConfig);
    })
    ->in('Feature');

