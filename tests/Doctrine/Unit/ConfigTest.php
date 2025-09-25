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
use Hyperf\Contract\ConfigInterface;
use Mine\Doctrine\Config;

beforeEach(function () {
    $this->configMock = Mockery::mock(ConfigInterface::class);
    $this->config = new Config($this->configMock);
});

describe('Config', function () {
    it('gets configuration value', function () {
        $this->configMock
            ->shouldReceive('get')
            ->with('doctrine.database.host')
            ->andReturn('localhost');

        expect($this->config->get('database.host'))->toBe('localhost');
    });

    it('gets full configuration when no key provided', function () {
        $expectedConfig = ['driver' => 'pdo_mysql', 'host' => 'localhost'];

        $this->configMock
            ->shouldReceive('get')
            ->with('doctrine')
            ->andReturn($expectedConfig);

        expect($this->config->get())->toBe($expectedConfig);
    });

    it('checks if configuration key exists', function () {
        $this->configMock
            ->shouldReceive('has')
            ->with('doctrine.database.host')
            ->andReturn(true);

        expect($this->config->has('database.host'))->toBeTrue();
    });

    it('returns false when configuration key does not exist', function () {
        $this->configMock
            ->shouldReceive('has')
            ->with('doctrine.nonexistent')
            ->andReturn(false);

        expect($this->config->has('nonexistent'))->toBeFalse();
    });

    it('checks full doctrine configuration exists when no key provided', function () {
        $this->configMock
            ->shouldReceive('has')
            ->with('doctrine')
            ->andReturn(true);

        expect($this->config->has())->toBeTrue();
    });

    it('sets configuration value', function () {
        $this->configMock
            ->shouldReceive('set')
            ->with('doctrine.database.host', 'newhost')
            ->once();

        $this->config->set('database.host', 'newhost');
    });

    it('handles null values gracefully', function () {
        $this->configMock
            ->shouldReceive('get')
            ->with('doctrine.null.value')
            ->andReturn(null);

        expect($this->config->get('null.value'))->toBeNull();
    });
});
