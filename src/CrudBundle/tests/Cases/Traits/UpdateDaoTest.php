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
use Mine\CrudBundle\Contract\UpdateDaoContract;
use Mine\CrudBundle\Tests\Stub\UserModel;
use Mine\CrudBundle\Traits\UpdateDaoTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class UpdateDaoTest extends TestCase implements UpdateDaoContract
{
    use UpdateDaoTrait;

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

    public function testSave(): void
    {
        // Prepare test data and mock necessary methods
        $data = [
            'name' => 'Test',
            'email' => 'test@example.com',
        ];
        $relations = [
            'profile' => [
                'name' => 'Test Profile',
            ],
        ];
        $relations1 = [
            'profile' => [
                [
                    'name' => 'Test Profile',
                ],
            ],
        ];
        $this->builder
            ->allows('create')
            ->once()
            ->with($data)
            ->andReturn(new UserModel($data));
        // Assert the result
        $this->assertInstanceOf(Model::class, $this->save($data));
        $this->builder
            ->allows('create')
            ->once()
            ->andReturn(new UserModel($data));
        $this->builder
            ->allows('getRelation')
            ->once()
            ->with('profile')
            ->andReturn($this->builder);
        $this->builder
            ->allows('save')
            ->once()
            ->with(Arr::get($relations, 'profile'))
            ->andReturn(new UserModel($relations['profile']));
        $this->assertInstanceOf(Model::class, $this->save($data, $relations));
        $this->builder
            ->allows('create')
            ->once()
            ->andReturn(new UserModel($data));
        $this->builder
            ->allows('getRelation')
            ->once()
            ->with('profile')
            ->andReturn($this->builder);
        $this->builder
            ->allows('insert')
            ->once()
            ->with(Arr::get($relations1, 'profile'))
            ->andReturn(new UserModel($relations1['profile']));
        $this->assertInstanceOf(Model::class, $this->save($data, $relations1));
    }

    public function testBatchSave(): void
    {
        // Prepare test data and mock necessary methods
        $data = [
            [
                'name' => 'Test 1',
                'email' => 'test1@example.com',
            ],
            [
                'name' => 'Test 2',
                'email' => 'test2@example.com',
            ],
        ];
        $this->builder
            ->allows('create')
            ->andReturnUsing(function ($item) {
                return new UserModel($item);
            });

        $result = $this->batchSave($data);

        // Assert the result
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(count($data), $result->count());
        $this->assertInstanceOf(Model::class, $result[0]);
        $this->assertInstanceOf(Model::class, $result[1]);
    }

    public function testInsert(): void
    {
        // Prepare test data and mock necessary methods
        $data = [
            'name' => 'Test',
            'email' => 'test@example.com',
        ];

        $this->builder->allows('insert')
            ->with($data)
            ->andReturn(true, false);
        // Assert the result
        $this->assertTrue($this->insert($data));
        $this->assertFalse($this->insert($data));

        // batch
        $batchData = [$data, $data];
        $this->builder
            ->allows('insert')
            ->with($batchData)
            ->andReturn(true, false);

        $this->assertTrue($this->insert($batchData));
        $this->assertFalse($this->insert($batchData));
    }
}
