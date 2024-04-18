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

namespace Mine\Support;

use Composer\Autoload\ClassLoader;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Support\Composer;

class Ip2region
{
    protected \XdbSearcher $searcher;

    /**
     * @see https://github.com/zoujingli/ip2region
     * @throws \Exception
     */
    public function __construct(protected ?StdoutLoggerInterface $logger = null)
    {
        $composerLoader = $this->getLoader();
        $path = $composerLoader->findFile(\XdbSearcher::class);

        $dbFile = dirname(realpath($path)) . '/ip2region.xdb';

        // 1、从 dbPath 加载整个 xdb 到内存。
        $cBuff = \XdbSearcher::loadContentFromFile($dbFile);
        if ($cBuff === null) {
            $this->logger?->error('failed to load content buffer from {db_file}', ['db_file' => $dbFile]);
            return;
        }
        $this->searcher = \XdbSearcher::newWithBuffer($cBuff);
    }

    public function getSearcher(): \XdbSearcher
    {
        return $this->searcher;
    }

    public function search(string $ip, bool $isSplitWords = true): ?string
    {
        $region = $this->getSearcher()->search($ip);

        if (! $region) {
            return null;
        }
        [$country, $number, $province, $city, $network] = explode('|', $region);
        if ($isSplitWords) {
            return $province . '-' . $city . ':' . $network;
        }
        return $country;
    }

    private function getLoader(): ClassLoader
    {
        return Composer::getLoader();
    }
}
