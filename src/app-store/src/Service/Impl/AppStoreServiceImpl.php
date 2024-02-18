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

namespace Xmo\AppStore\Service\Impl;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Symfony\Component\Finder\Finder;
use Xmo\AppStore\Service\AppStoreService;

use function Hyperf\Support\env;
use function Hyperf\Translation\trans;

final class AppStoreServiceImpl implements AppStoreService
{
    private readonly Client $client;

    private readonly array $config;

    public function __construct(
        ClientFactory $clientFactory,
        ConfigInterface $config
    ) {
        $this->client = $clientFactory->create([
            'base_uri' => 'https://www.mineadmin.com/server/store/',
            'timeout' => 10.0,
        ]);
        $this->config = $config->get('mine-extension');
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function request(string $uri, array $data = []): array
    {
        $response = $this->client->post($uri, [
            'json' => $data,
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(trans('app-store.store.response_fail'));
        }
        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }
        return $result;
    }

    /**
     * Get all locally extensions.
     * @throws \JsonException
     */
    public function getLocalExtensions(): array
    {
        $extensionPath = $this->getLocalExtensionsPath();
        if (! is_dir($extensionPath)) {
            return [];
        }
        $finder = Finder::create()
            ->files()
            ->name('mine.json')
            ->in($extensionPath);
        if (! $finder->hasResults()) {
            return [];
        }
        $result = [];
        foreach ($finder as $file) {
            $path = $file->getPath();
            /*
             * @var \SplFileInfo $file
             */
            $info = $this->readExtensionInfo($path);
            $info['status'] = file_exists($path . '/install.lock');
            $result[] = $info;
        }
        return $result;
    }

    /**
     * Check if the given parameter is a valid configuration item.
     */
    public function checkPlugin(array $info): bool
    {
        $checkOption = [
            'name', 'description', 'author', 'require', 'description', 'dependencies',
            'installScript', 'uninstallScript',
        ];
        foreach ($checkOption as $option) {
            // Checking mine.json configuration
            if (! array_key_exists($option, $info)) {
                $this->throwCheckExtension($info['name'] ?? '--');
            }
            $installScript = $info['installScript'];
            $uninstallScript = $info['uninstallScript'];
            if (! class_exists($installScript) || ! class_exists($uninstallScript)) {
                throw new \RuntimeException(
                    sprintf('Installation and uninstallation scripts configured with the extension %s do not take effect and are not valid classes.', $info['name'] ?? '--')
                );
            }
        }
        return true;
    }

    /**
     * Get local plugin directory.
     */
    public function getLocalExtensionsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . $this->getExtensionDirectory();
    }

    /**
     * Get MineAdmin developer credentials.
     */
    private function getAccessToken(): string
    {
        $accessToken = $this->config['access_token'] ?? env('MINE_ACCESS_TOKEN');
        if (empty($accessToken)) {
            throw new \RuntimeException(trans('app-store.access_token_null'));
        }
        return (string) $accessToken;
    }

    /**
     * Read the specified directory to get the extension details.
     * @throws \JsonException
     */
    private function readExtensionInfo(string $path): array
    {
        $extensionJson = $path . DIRECTORY_SEPARATOR . 'mine.json';
        if (! file_exists($extensionJson)) {
            throw new \RuntimeException(sprintf('The catalog %s is not a valid mine extension,because it\'s missing the necessary mine.json file.', $path));
        }
        $info = json_decode(file_get_contents($extensionJson), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('Error reading mine.json file in %s,' . json_last_error_msg(), $path));
        }
        $this->checkPlugin($info);
        return $info;
    }

    private function throwCheckExtension(string $path): void
    {
        throw new \RuntimeException(sprintf('The catalog %s is not a valid mine extension package because the mine.json file it belongs to is missing key configurations such as `name` `author` `dependencies` `description` `require` `installScript` `uninstallScript` and so on.', $path));
    }

    private function getBasePath(): string
    {
        return BASE_PATH;
    }

    private function getExtensionDirectory(): string
    {
        return 'plugin';
    }
}
