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
use Mine\AppStore\Packer\JsonPacker;
use Mine\AppStore\Packer\PackerFactory;

beforeEach(function () {
    $this->mock = ApplicationContext::getContainer()->get(PackerFactory::class);
});

test('factory', function () {
    expect($this->mock)->toBeInstanceOf(PackerFactory::class);
    try {
        $this->mock->get('demo');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toEqual(sprintf('%s Packer type not found', 'demo'));
    }
    $jsonPacker = $this->mock->get();
    expect($jsonPacker)->toBeInstanceOf(JsonPacker::class);
    expect($jsonPacker->unpack('{"a":1}'))->toEqual(['a' => 1]);
    expect($jsonPacker->pack(['a' => 1]))->toEqual('{"a":1}');
});
