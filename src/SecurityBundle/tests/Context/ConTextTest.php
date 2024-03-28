<?php

namespace Mine\SecurityBundle\Tests\Context;

use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use Mine\SecurityBundle\Context\Context;
use PHPUnit\Framework\TestCase;

class ConTextTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testAll(): void
    {
        $context = new ConText();
        $context->set('key', 'value');
        $this->assertEquals('value', $context->get('key'));
        $this->assertEquals(null, $context->get('not_exist'));
        $this->assertEquals('xxx', $context->get('not_exist', 'xxx'));
        $this->assertEquals('xxx', $context->getOrSet('not_exist', 'xxx'));
        $this->assertEquals('xxx2', $context->getOrSet('not_exist1', function () {
            return 'xxx2';
        }));
        $this->assertTrue($context->has('key'));
        $this->assertFalse($context->has('key2'));
    }
}