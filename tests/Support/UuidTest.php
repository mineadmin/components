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
    expect($uuid)->toEqual('1234567890');
});

test('testGetUuidInCoroutine', function () {
    if (Coroutine::inCoroutine()) {
        $uuid = UuidRequestIdProcessor::getUuid();
        Coroutine::create(function () use ($uuid) {
            expect(UuidRequestIdProcessor::getUuid())->toEqual($uuid);
        });
    } else {
        Coroutine::create(function () {
            $uuid = UuidRequestIdProcessor::getUuid();
            Coroutine::create(function () use ($uuid) {
                expect(UuidRequestIdProcessor::getUuid())->toEqual($uuid);
            });
        });
    }
});

test('testAutoSetUuid', function () {
    UuidRequestIdProcessor::setUuid('11233123');
    expect(UuidRequestIdProcessor::getUuid())->toEqual('11233123');
});

test('testAutoSetUuidInCoroutine', function () {
    Coroutine::create(function () {
        UuidRequestIdProcessor::setUuid('11233123');
        expect(UuidRequestIdProcessor::getUuid())->toEqual('11233123');
    });
});

test('testAutoSetUuidInParent', function () {
    Coroutine::create(function () {
        UuidRequestIdProcessor::setUuid('11233123');
        expect(UuidRequestIdProcessor::getUuid())->toEqual('11233123');
        Coroutine::create(function () {
            expect(UuidRequestIdProcessor::getUuid())->toEqual('11233123');
        });
    });
});
