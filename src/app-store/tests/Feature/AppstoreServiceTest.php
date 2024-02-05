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
use Xmo\AppStore\Service\Impl\AppStoreServiceImpl;

beforeEach(function () {
    $this->mock = ApplicationContext::getContainer()->get(AppStoreServiceImpl::class);
});
it('app store', function () {
    expect(true)->toBeTrue();
});
