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

namespace Mine\Security\Http\Tests\Cases\Jwt\Black;

use Mine\Security\Http\Jwt\Black\CacheBlack;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 * @coversNothing
 */
class CacheBlackTest extends TestCase
{
    private CacheBlack $cacheBlack;

    private CacheInterface $cacheProphecy;

    protected function setUp(): void
    {
        $this->cacheProphecy = \Mockery::mock(CacheInterface::class);
        $this->cacheBlack = new CacheBlack($this->cacheProphecy);
    }

    public function testStorageAdd(): void
    {
        $cacheKey = 'testKey';
        $val = ['testVal'];
        $tokenCacheTime = 3600;
        $prefix = 'testPrefix';

        $this->cacheProphecy->allows('set')
            ->with($prefix . ':' . $cacheKey, $val, $tokenCacheTime)
            ->andReturn(true);

        $result = $this->cacheBlack->storageAdd($cacheKey, $val, $tokenCacheTime, $prefix);
        $this->assertTrue($result);
    }

    public function testStorageGet(): void
    {
        $cacheKey = 'testKey';
        $val = ['testVal'];
        $prefix = 'testPrefix';
        $this->cacheProphecy->allows('get')->andReturn($prefix . ':' . $cacheKey)->andReturn($val);
        $result = $this->cacheBlack->storageGet($cacheKey, $prefix);
        $this->assertSame($val, $result);
    }

    public function testStorageDelete(): void
    {
        $cacheKey = 'testKey';
        $prefix = 'testPrefix';
        $this->cacheProphecy->allows('delete')->with($prefix . ':' . $cacheKey)->andReturn(true);
        $result = $this->cacheBlack->storageDelete($cacheKey, $prefix);
        $this->assertTrue($result);
    }

    public function testStorageClear(): void
    {
        $prefix = 'testPrefix';
        $this->cacheProphecy->allows('delete')->with($prefix . ':*')->andReturn(true);
        $result = $this->cacheBlack->storageClear($prefix);
        $this->assertTrue($result);
    }
}
