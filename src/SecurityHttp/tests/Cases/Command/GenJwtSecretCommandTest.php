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

namespace Mine\Security\Http\Tests\Cases\Command;

use Hyperf\Stringable\Str;
use Mine\Security\Http\Command\GenJwtSecretCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\Input;

/**
 * @internal
 * @coversNothing
 */
class GenJwtSecretCommandTest extends TestCase
{
    public function testGenerator(): void
    {
        $reflection = new \ReflectionClass(GenJwtSecretCommand::class);
        $invoke = $reflection->getMethod('generator');
        $this->assertIsString($invoke->invoke(\Mockery::mock(GenJwtSecretCommand::class)));
    }

    public function testGetSecretName(): void
    {
        $reflection = new \ReflectionClass(GenJwtSecretCommand::class);
        $invoke = $reflection->getMethod('getSecretName');
        $instance = $reflection->newInstanceWithoutConstructor();
        $input = \Mockery::mock(Input::class);
        $input->allows('getOption')->andReturn('xxx');
        $instance->setInput($input);
        $this->assertSame('XXX', $invoke->invoke($instance));
    }

    public function testGetEnvPath(): void
    {
        $reflection = new \ReflectionClass(GenJwtSecretCommand::class);
        $invoke = $reflection->getMethod('getEnvPath');
        $m = \Mockery::mock(GenJwtSecretCommand::class);
        $this->assertSame(BASE_PATH . '/.env', $invoke->invoke($m));
    }

    public function testInvoke(): void
    {
        $reflection = new \ReflectionClass(GenJwtSecretCommand::class);
        $invoke = $reflection->getMethod('__invoke');
        $m = \Mockery::mock(GenJwtSecretCommand::class);
        $m->shouldAllowMockingProtectedMethods();
        $env = sys_get_temp_dir() . '/' . Str::random(32) . '.env';
        file_put_contents($env, "xxx=1\n");
        $m->allows('getEnvPath')->andReturn($env);
        $input = \Mockery::mock(Input::class);
        $input->allows('getOption')->andReturn('Demo');
        $m->allows('setInput');
        $m->allows('getSecretName')->andReturn('DEMO');
        $m->allows('generator')->andReturn(base64_encode(random_bytes(64)));
        $m->allows('info')->andReturnUsing(function ($v) {
            echo $v;
        });
        $m->setInput($input);
        $invoke->invoke($m);
        $this->assertTrue(str_contains(file_get_contents($env), 'DEMO'));
    }
}
