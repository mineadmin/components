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

namespace Mine\CrudBundle\Tests\Cases\Abstracts;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use Mine\CrudBundle\Tests\Stub\UserDao;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AbstractDaoTest extends TestCase
{
    public function testGetModel(): void
    {
        $dao = new UserDao();
        $model = $dao->getModel();
        $this->assertInstanceOf(Model::class, $model);
    }

    public function testGetModelQuery(): void
    {
        $connection = \Mockery::mock(ConnectionInterface::class);
        $connectionResolver = \Mockery::mock(ConnectionResolverInterface::class);
        Register::setConnectionResolver($connectionResolver);
        $connectionResolver->allows('connection')
            ->andReturn($connection);
        $connection->allows('getQueryGrammar')
            ->andReturn(new Grammar());
        $connection->allows('getPostProcessor')
            ->andReturn(new Processor());
        $dao = new UserDao();
        $query = $dao->getModelQuery();

        $this->assertInstanceOf(Builder::class, $query);
    }
}
