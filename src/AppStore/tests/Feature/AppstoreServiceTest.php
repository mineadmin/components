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
use GuzzleHttp\Client;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Guzzle\ClientFactory;
use Mine\AppStore\Service\Impl\AppStoreServiceImpl;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {
    putenv('MINE_ACCESS_TOKEN=xxxx1');
    ApplicationContext::getContainer()->set(TranslatorInterface::class, new class implements TranslatorInterface {
        public function trans(string $key, array $replace = [], ?string $locale = null): array|string
        {
            return 'xxx';
        }

        public function transChoice(string $key, $number, array $replace = [], ?string $locale = null): string
        {
            return '';
        }

        public function getLocale(): string
        {
            return 'zh-CN';
        }

        public function setLocale(string $locale)
        {
            // TODO: Implement setLocale() method.
        }
    });
    $mockClientFactory = Mockery::mock(ClientFactory::class);
    $mockClient = Mockery::mock(Client::class);
    $response = Mockery::mock(ResponseInterface::class);
    $response->allows('getStatusCode')->andReturn(200, 500);
    $content = Mockery::mock(StreamInterface::class);
    $remoteResult = [
        'data' => [],
        'message' => 'success',
    ];
    $result = json_encode($remoteResult, JSON_UNESCAPED_UNICODE);
    $content->allows('getContents')
        ->andReturn($result, $result, '');
    $response->allows('getBody')->andReturn($content);
    $mockClient->allows('post')
        ->andReturn($response);
    $mockClientFactory->allows('create')->andReturn($mockClient);
    ApplicationContext::getContainer()->set(ClientFactory::class, $mockClientFactory);
    ApplicationContext::getContainer()->set(ConfigInterface::class, new class implements ConfigInterface {
        public function get(string $key, mixed $default = null): mixed
        {
            return [];
        }

        public function has(string $keys): bool
        {
            return true;
        }

        public function set(string $key, mixed $value): void {}
    });
    $this->mock = ApplicationContext::getContainer()->get(AppStoreServiceImpl::class);
});
test('request test', function () {
    expect(true)->toBeTrue();
    $response = $this->mock->request('/aaaa', ['xxx' => 'xxx']);
    expect($response)->toBeArray();
    try {
        $response = $this->mock->request('/aaaa', ['xxx' => 'xxx']);
    } catch (RuntimeException $e) {
        expect($e)->toBeInstanceOf(RuntimeException::class);
    }
    try {
        $response = $this->mock->request('/aaaa', ['xxx' => 'xxx']);
    } catch (RuntimeException $e) {
        expect($e)->toBeInstanceOf(RuntimeException::class);
    }
});
