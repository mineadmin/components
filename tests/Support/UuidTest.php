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
use Hyperf\Coroutine\Coroutine;
use Mine\Support\Logger\UuidRequestIdProcessor;

test('testSetUuid', function () {
    $uuid = UuidRequestIdProcessor::setUuid('1234567890');
    $this->assertEquals('1234567890', $uuid);
});

test('testGetUuidInCoroutine', function () {
    if (Coroutine::inCoroutine()) {
        $uuid = UuidRequestIdProcessor::getUuid();
        Coroutine::create(function () use ($uuid) {
            $this->assertEquals($uuid, UuidRequestIdProcessor::getUuid());
        });
    } else {
        Coroutine::create(function () {
            $uuid = UuidRequestIdProcessor::getUuid();
            Coroutine::create(function () use ($uuid) {
                $this->assertEquals($uuid, UuidRequestIdProcessor::getUuid());
            });
        });
    }
});

test('testAutoSetUuid', function () {
    UuidRequestIdProcessor::setUuid('11233123');
    $this->assertEquals('11233123', UuidRequestIdProcessor::getUuid());
});

test('testAutoSetUuidInCoroutine', function () {
    Coroutine::create(function () {
        UuidRequestIdProcessor::setUuid('11233123');
        $this->assertEquals('11233123', UuidRequestIdProcessor::getUuid());
    });
});

test('testAutoSetUuidInParent', function () {
    Coroutine::create(function () {
        UuidRequestIdProcessor::setUuid('11233123');
        $this->assertEquals('11233123', UuidRequestIdProcessor::getUuid());
        Coroutine::create(function () {
            $this->assertEquals('11233123', UuidRequestIdProcessor::getUuid());
        });
    });
});
