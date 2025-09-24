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
use Mine\Crontab\CrontabContainer;

test('container', static function () {
    CrontabContainer::set('id', 'xxx');
    expect(CrontabContainer::get('id'))->toBe('xxx');
    expect(CrontabContainer::has('id'))->toBeTrue();
    expect(CrontabContainer::has('test'))->toBeFalse();
});
