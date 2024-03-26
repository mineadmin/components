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

namespace Mine\Crontab;

use App\Setting\Model\SettingCrontab;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Redis\Redis;
use Mine\MineModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Config\config;

/**
 * 定时任务管理器
 * Class MineCrontabManage.
 */
class MineCrontabManage
{
    /**
     * ContainerInterface.
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     * Parser.
     */
    #[Inject]
    protected Parser $parser;

    /**
     * ClientFactory.
     */
    #[Inject]
    protected ClientFactory $clientFactory;

    /**
     * Redis.
     */
    protected Redis $redis;

    /**
     * MineCrontabManage constructor.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->redis = redis();
    }

    /**
     * 获取定时任务列表.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCrontabList(): array
    {
        $prefix = config('cache.default.prefix');
        //        $data = $this->redis->get($prefix . 'crontab');

        //        if ($data === false) {
        $data = SettingCrontab::query()
            ->where('status', MineModel::ENABLE)
            ->get(explode(',', 'id,name,type,target,rule,parameter'))->toArray();
        $this->redis->set($prefix . 'crontab', serialize($data));
        //        } else {
        //            $data = unserialize($data);
        //        }

        if (is_null($data)) {
            return [];
        }

        $last = time();
        $list = [];

        foreach ($data as $item) {
            $crontab = new MineCrontab();
            $crontab->setCallback($item['target']);
            $crontab->setType((string) $item['type']);
            $crontab->setEnable(true);
            $crontab->setCrontabId($item['id']);
            $crontab->setName($item['name']);
            $crontab->setParameter($item['parameter'] ?: '');
            $crontab->setRule($item['rule']);

            if (! $this->parser->isValid($crontab->getRule())) {
                console()->info('Crontab task [' . $item['name'] . '] rule error, skipping execution');
                continue;
            }

            $time = $this->parser->parse($crontab->getRule(), $last);
            if ($time) {
                foreach ($time as $t) {
                    $list[] = clone $crontab->setExecuteTime($t);
                }
            }
        }
        return $list;
    }
}
