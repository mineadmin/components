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

namespace Mine\CrudBundle\Tests\Cases\Traits;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\CrudBundle\Contract\DeleteDaoContract;
use Mine\CrudBundle\Tests\Stub\UserModel;
use Mine\CrudBundle\Traits\DeleteDaoTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DeleteDaoTest extends TestCase implements DeleteDaoContract
{
    use DeleteDaoTrait;

    private $modelInstance;

    private $builder;

    protected function setUp(): void
    {
        $this->modelInstance = \Mockery::mock(UserModel::class);
        $this->builder = \Mockery::mock(Builder::class);
        $this->builder->allows('getModel')->andReturn($this->modelInstance);
        $this->connection = \Mockery::mock(ConnectionInterface::class);
        $connectionResolverInterface = \Mockery::mock(ConnectionResolverInterface::class);
        $connectionResolverInterface
            ->allows('connection')
            ->andReturn($this->connection);
        ApplicationContext::getContainer()
            ->set(ConnectionResolverInterface::class, $connectionResolverInterface);
        $this->connection->allows('transaction')
            ->andReturnUsing(function ($callback) {
                try {
                    return $callback();
                } catch (\Exception $exception) {
                    return false;
                }
            });
    }

    public function getModel(): Model
    {
        return $this->modelInstance;
    }

    public function getModelQuery(): Builder
    {
        return $this->builder;
    }

    public function testDeleteWithIdWhere()
    {
        $id = 1;
        $this->modelInstance->allows('getKeyName')->andReturn('id');
        $this->builder->allows('where')->withArgs(function ($key, $value) use ($id) {
            $this->assertEquals('id', $key);
            $this->assertEquals($id, $value);
            return true;
        })->andReturn($this->builder);
        $this->builder->allows('delete')->andReturn(true, false);
        $this->assertTrue($this->delete($id));
        $this->assertFalse($this->delete($id));
    }

    public function testDeleteWithArrayWhere(): void
    {
        $this->modelInstance->allows('getKeyName')->andReturn('id');
        $idOrWhere = [1, 2];
        $this->builder
            ->allows('whereIn')
            ->withArgs(function ($key, $values) use ($idOrWhere) {
                $this->assertEquals('id', $key);
                $this->assertEquals($idOrWhere, $values);
                return true;
            })->andReturn($this->builder);
        $this->builder->allows('delete')->andReturn(true, false);
        $this->assertTrue($this->delete($idOrWhere));
        $this->assertFalse($this->delete($idOrWhere));
    }

    public function testDeleteWithCallableWhere(): void
    {
        $this->modelInstance->allows('getKeyName')->andReturn('id');
        $where = function ($query) {
            $query->where('id', 1);
        };
        $this->builder->allows('where')->withArgs(function ($callable) use ($where) {
            $this->assertEquals($callable, $where);
            return true;
        })->andReturn($this->builder);
        $this->builder->allows('delete')->andReturn(true, false);
        $this->assertTrue($this->delete($where));
        $this->assertFalse($this->delete($where));
    }

    public function testRemoveWithCallableWhere(): void
    {
        // Arrange
        $idOrWhere = function ($query) {
            $query->where('id', 1);
        };
        $this->builder->allows('where')->withArgs(function ($callable) use ($idOrWhere) {
            $this->assertEquals($callable, $idOrWhere);
            return true;
        })->andReturn($this->builder);
        $this->builder->allows('first')->andReturn($this->modelInstance, null);
        $this->modelInstance->allows('forceDelete')->andReturn(true);
        $this->modelInstance->allows('delete')->andReturn(true);
        $this->assertTrue($this->remove($idOrWhere));
        $this->assertFalse($this->remove($idOrWhere));
    }

    public function testRemoveWithId(): void
    {
        $id = 1;
        $this->builder
            ->allows('find')
            ->withArgs(function ($key) use ($id) {
                $this->assertEquals($key, $id);
                return true;
            })->andReturn($this->modelInstance);
        $this->modelInstance
            ->allows('forceDelete')
            ->andReturn(true);

        $this->assertFalse($this->remove($id));
        $this->assertTrue($this->remove($id, true));
    }

    public function testRemoveByIds(): void
    {
        $ids = [1, 2, 3];
        $this->modelInstance->allows('getKeyName')->andReturn('id');
        $this->builder
            ->allows('whereIn')
            ->withArgs(function ($key, $values) use ($ids) {
                $this->assertEquals('id', $key);
                $this->assertEquals($ids, $values);
                return true;
            })->andReturn($this->builder);
        $this->builder->allows('delete')->andReturn(true);
        $this->removeByIds($ids);
    }
}
