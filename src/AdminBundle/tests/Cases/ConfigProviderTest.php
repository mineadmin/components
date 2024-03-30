<?php

namespace Mine\Admin\Bundle\Tests\Cases;

use Mine\Admin\Bundle\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function test__invoke(): void
    {
        $this->assertIsArray((new ConfigProvider())());
    }
}