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

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\CrudBundle\Contract\SaveOrUpdateDaoContract;
use Mine\CrudBundle\Tests\Stub\UserModel;
use Mine\CrudBundle\Traits\SaveOrUpdateDaoTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SaveOrUpdateDaoTest extends TestCase implements SaveOrUpdateDaoContract
{
    use SaveOrUpdateDaoTrait;

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
                    $this->fail($exception);
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

    public function testSaveOrUpdate(): void
    {
        $this->modelInstance->allows('getKeyName')->andReturn('id');

        // Mock data and where condition
        $data = ['name' => 'test', 'email' => 'test@example.com'];
        $where = ['id' => 1];
        $this->builder->allows('updateOrCreate')
            ->once()
            ->withArgs(function (array $attributes, array $values) use ($data, $where): bool {
                $this->assertEquals($data, $values);
                $this->assertEquals($where, $attributes);
                return true;
            })->andReturn($this->modelInstance);
        $this->assertInstanceOf(Model::class, $this->saveOrUpdate($data, $where));
        $data['id'] = 1;
        $this->builder->allows('updateOrCreate')
            ->once()
            ->withArgs(function (array $attributes) use ($data): bool {
                $this->assertEquals(Arr::only($data, ['id']), $attributes);
                return true;
            })->andReturn($this->modelInstance);
        $this->assertInstanceOf(Model::class, $this->saveOrUpdate($data));
    }

    public function testBatchSaveOrUpdate(): void
    {
        // Mock data and where keys
        $data = [
            ['id' => 1, 'name' => 'test1', 'email' => 'test1@example.com'],
            ['id' => 1, 'name' => 'test2', 'email' => 'test2@example.com'],
        ];
        $whereKeys = ['id'];
        $this->modelInstance->allows('getKeyName')->andReturn('id');
        $this->builder->allows('updateOrCreate')
            ->times(2)
            ->withArgs(function (array $attributes, array $values): bool {
                $this->assertEquals([
                    'id',
                ], array_keys($attributes));
                $this->assertEquals([
                    'name', 'email',
                ], array_keys($values));
                return true;
            })->andReturn($this->modelInstance)
            ->withArgs(function (array $attributes, array $values): bool {
                $this->assertEquals([
                    'id',
                ], array_keys($attributes));
                $this->assertEquals([
                    'name', 'email',
                ], array_keys($values));
                return true;
            })->andReturn($this->modelInstance);
        $this->assertInstanceOf(Collection::class, $this->batchSaveOrUpdate($data, $whereKeys));
        $this->assertInstanceOf(Collection::class, $this->batchSaveOrUpdate($data));
    }
}
