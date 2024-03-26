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

namespace Mine\Crontab\Tests\Cases\Command;

use Hyperf\Database\Migrations\Migrator;
use Mine\Crontab\Command\CrontabMigrateCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 * @coversNothing
 */
class CrontabMigrateCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $reflectionClass = new \ReflectionClass(CrontabMigrateCommand::class);
        $reflectionClass->newInstance(\Mockery::mock(Migrator::class));
        $this->assertTrue(true);
    }

    public function testInvoke(): void
    {
        $reflectionClass = new \ReflectionClass(CrontabMigrateCommand::class);
        $migrator = \Mockery::mock(Migrator::class);
        $migrator->allows('setOutput')->andReturnUsing(function ($output) use ($migrator) {
            $this->assertInstanceOf(NullOutput::class, $output);
            return $migrator;
        });
        $migrator->allows('run')->andReturnUsing(function ($path) {
            $this->assertIsString($path);
            $this->assertSame($path, dirname(__DIR__, 3) . '/Database/Migrations');
            return [];
        });
        $migrator->allows('setConnection')
            ->andReturnUsing(function ($connection) {
                $this->assertSame($connection, 'default');
            }, function ($connection) {
                $this->assertSame($connection, 'test');
            });
        /**
         * @var CrontabMigrateCommand $instance
         */
        $instance = $reflectionClass->newInstance($migrator);
        $inputMock = \Mockery::mock(InputInterface::class);
        $inputMock->allows('getOption')->andReturn(null, 'test');
        $instance->setInput($inputMock);
        $instance();
        $instance();
    }
}
