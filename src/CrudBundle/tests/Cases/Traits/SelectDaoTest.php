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

use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Mine\CrudBundle\Contract\PageDaoContract;
use Mine\CrudBundle\Tests\Stub\UserModel;
use Mine\CrudBundle\Traits\SelectDaoTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SelectDaoTest extends TestCase implements PageDaoContract
{
    use SelectDaoTrait;

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

    public function handleSearch(Builder $query, mixed $params = null): Builder
    {
        if (isset($params['key'])) {
            $this->assertEquals('value', $params['key']);
        }
        return $query;
    }

    public function testPage(): void
    {
        $params = ['key' => 'value'];
        $page = 1;
        $size = 10;
        $this->builder
            ->allows('select')
            ->once()
            ->andReturn($this->builder);
        $this->builder
            ->allows('paginate')
            ->once()
            ->andReturn(\Mockery::mock(LengthAwarePaginatorInterface::class));
        $this->assertInstanceOf(LengthAwarePaginatorInterface::class, $this->page($params, $page, $size));
    }

    public function testCount(): void
    {
        $params = ['key' => 'value'];
        $count = 10;
        $this->builder->allows('count')->once()->andReturn($count);
        $result = $this->total($params);
        $this->assertEquals($count, $result);
    }

    public function testList(): void
    {
        $params = ['key' => 'value'];
        $this->builder->allows('get')
            ->andReturn(Collection::make([
                ['id' => 1, 'name' => 'satan'],
                ['id' => 2, 'name' => 'mine'],
            ]), Collection::make([]));
        $result = $this->list($params);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $result2 = $this->list($params);
        $this->assertInstanceOf(Collection::class, $result2);
        $this->assertCount(0, $result2);
    }

    public function testGetById(): void
    {
        $id = 1;
        $this->builder->allows('take')->with(1)->andReturn($this->builder);
        $this->builder
            ->allows('find')
            ->withArgs(function ($val) use ($id) {
                $this->assertEquals($id, $val);
                return true;
            })
            ->andReturn(null, $this->modelInstance);
        $this->assertNull($this->findById($id));
        $result = $this->findById($id);
        $this->assertInstanceOf(UserModel::class, $result);
        $this->assertEquals($this->modelInstance, $result);
    }
}
