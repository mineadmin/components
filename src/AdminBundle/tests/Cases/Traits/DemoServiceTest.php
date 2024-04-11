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

namespace Mine\Admin\Bundle\Tests\Cases\Traits;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Model;
use Mine\Admin\Bundle\Traits\ServiceTrait;
use Mine\CrudBundle\Abstracts\CrudDao;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DemoServiceTest extends TestCase
{
    use ServiceTrait;

    protected function setUp(): void
    {
        $this->dao = \Mockery::mock(CrudDao::class);
    }

    public function testLst(): void
    {
        $pageInstance = \Mockery::mock(LengthAwarePaginatorInterface::class);
        $pageInstance->allows('items')->andReturn([]);
        $pageInstance->allows('currentPage')->andReturns(1, 20);
        $pageInstance->allows('total')->andReturn(0);
        $params = ['xxx' => 1];
        $this->dao->allows('page')->with($params, 1, 10)->andReturn($pageInstance);
        $this->assertSame([
            $this->getLstTotalField() => 0,
            $this->getLstCurrentPageField() => 1,
            $this->getLstListField() => [],
        ], $this->lst($params, 1, 10));
        $params = ['x1' => 1];
        $page = 20;
        $size = 100;
        $this->dao->allows('page')
            ->with($params, $page, $size)
            ->andReturn($pageInstance);
        $this->assertSame([
            $this->getLstTotalField() => 0,
            $this->getLstCurrentPageField() => 20,
            $this->getLstListField() => [],
        ], $this->lst($params, $page, $size));
    }

    public function testRecycle(): void
    {
        $pageInstance = \Mockery::mock(LengthAwarePaginatorInterface::class);
        $pageInstance->allows('items')->andReturn([]);
        $pageInstance->allows('currentPage')->andReturns(1, 20);
        $pageInstance->allows('total')->andReturn(0);
        $params = ['xxx' => 1];
        $this->dao->allows('page')->with(['xxx' => 1, 'recycle' => true], 1, 10)->andReturn($pageInstance);
        $this->assertSame([
            $this->getLstTotalField() => 0,
            $this->getLstCurrentPageField() => 1,
            $this->getLstListField() => [],
        ], $this->recycle($params, 1, 10));
    }

    public function testSave(): void
    {
        $model = new class() extends Model {
            public function toArray(): array
            {
                return [];
            }
        };
        $data = ['xxx' => 1];
        $this->dao->allows('save')->andReturn($data)->andReturn($model);
        $this->assertSame([], $this->save($data));
    }

    public function testUpdate(): void
    {
        $id = 1;
        $data = ['xxx' => 1];
        $isModel = false;
        $this->dao->allows('update')->with($id, $data, $isModel)->andReturn(true);
        $this->assertTrue($this->update($id, $data, $isModel));
        $id = 2;
        $data = ['xxx' => 2];
        $isModel = true;
        $this->dao->allows('update')->with($id, $data, $isModel)->andReturn(false);
        $this->assertFalse($this->update($id, $data, $isModel));
    }

    public function testChangeStatus(): void
    {
        $id = 1;
        $this->dao->allows('update')->with($id, [
            'status' => 1,
        ], true)->andReturn(true);
        $this->assertTrue($this->changeStatus($id, 1));
    }

    public function testDelete(): void
    {
        $this->dao->allows('remove')->with(1)->andReturn(true);
        $this->assertTrue($this->delete(1));
        $this->dao->allows('remove')->andReturn(true);
        $this->assertTrue($this->delete([1, 2, 3]));
    }

    public function testRealDelete(): void
    {
        $this->dao->allows('delete')->with(1)->andReturn(true);
        $this->assertTrue($this->realDelete(1));

        $this->dao->allows('delete')->with('1')->andReturn(true);
        $this->assertTrue($this->realDelete('1'));
        $this->dao->allows('delete')->with([1])->andReturn(true);
        $this->assertTrue($this->realDelete([1]));
    }
}
