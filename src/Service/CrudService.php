<?php

namespace Plugin\MineAdmin\PayGateway\Service;

use Hyperf\Codec\Json;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Redis\Redis;
use Plugin\MineAdmin\PayGateway\Model\Enums\PayConfig\Channel;
use Plugin\MineAdmin\PayGateway\Model\PayConfig;

class CrudService
{

    /**
     * 全局配置项key
     */
    public const GLOBAL_SETTING_KEY = 'mineadmin:plugin:pay_config';

    public function __construct(
        private readonly Redis $redis
    ){}

    /**
     * 获取全局配置
     */
    public function getGlobalSetting(): array
    {
      $default = [
          'logger' => [
              'enable' => false,
              'group'   =>  'default'
          ],
          'http' => [ // optional
              'timeout' => 5.0,
              'connect_timeout' => 5.0,
          ],
      ];

      $value = $this->redis->get(self::GLOBAL_SETTING_KEY);
      if (!$value){
          return $default;
      }
      return Json::decode($value);
    }

    /**
     * 设置全局配置
     */
    public function setGlobalSetting(array $data): bool
    {
        return (bool)$this->redis->set(self::GLOBAL_SETTING_KEY,Json::encode($data));
    }

    /**
     * 列表查询
     * @param array $params
     * @return array
     */
    public function page(array $params): array
    {
        return PayConfig::query()
            // 名称
            ->when(Arr::get($params,'name'),function (Builder $builder,string $name){
                $builder->where('name','like',$name.'%');
            })
            // 通道筛选
            ->when(Arr::get($params,'channel'),function (Builder $builder,Channel $channel){
                $builder->where('channel',$channel);
            })
            // 创建时间筛选
            ->when(Arr::get($params,'created_at'),function (Builder $builder,array $createdAt){
                $builder->whereBetween('created_at',$createdAt);
            })
            // 更新时间筛选
            ->when(Arr::get($params,'updated_at'),function (Builder $builder,array $createdAt){
                $builder->whereBetween('updated_at',$createdAt);
            })
            ->forPage($params['page'] ?? 1,$params['pageSize'] ?? 10)
            ->get()->toArray();
    }

    /**
     * 新增
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        return (bool)PayConfig::create($data);
    }

    /**
     * 保存
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function save(int $id,array $data): bool
    {
        $entity = PayConfig::findOrFail($id);
        return $entity->fill($data)->save();
    }

    /**
     * 删除
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        return PayConfig::query()->whereIn('id',$ids)->delete() > 0;
    }

    public function toConfigArray(): array
    {
        return PayConfig::toPayConfig();
    }

}