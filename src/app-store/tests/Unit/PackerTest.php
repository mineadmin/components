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
use Xmo\AppStore\Packer\JsonPacker;
use Xmo\AppStore\Packer\PackerFactory;

beforeEach(function () {
    $this->mock = ApplicationContext::getContainer()->get(PackerFactory::class);
});

test('factory', function () {
    $this->assertInstanceOf(PackerFactory::class, $this->mock);
    try {
        $this->mock->get('demo');
    } catch (RuntimeException $e) {
        $this->assertEquals(sprintf('%s Packer type not found', 'demo'), $e->getMessage());
    }
    $jsonPacker = $this->mock->get();
    $this->assertInstanceOf(JsonPacker::class, $jsonPacker);
    $this->assertEquals(['a' => 1], $jsonPacker->unpack('{"a":1}'));
    $this->assertEquals('{"a":1}', $jsonPacker->pack(['a' => 1]));
});
