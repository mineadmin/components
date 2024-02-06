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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Mine\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(function () {
        $mockConfig = Mockery::mock(ConfigInterface::class);
        $mockConfig->allows('has')->andReturn(true);
        $mockConfig->allows('get')->andReturn([]);
        $mockConfig->allows('set')->andReturn(true);
        ApplicationContext::getContainer()
            ->set(ConfigInterface::class, $mockConfig);
    })
    ->in('Feature');
