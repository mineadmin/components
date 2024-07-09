<?php

namespace Plugin\MineAdmin\PayGateway\Model;

use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Model;
use Plugin\MineAdmin\PayGateway\Model\Enums\PayConfig\Channel;

/**
 * @property-read int $id
 * @property-read string $name 通道配置名称
 * @property-read Channel $channel 通道类型
 * @property-read array $config 通道配置
 * @property-read Carbon $created_at 创建时间
 * @property-read Carbon $updated_at 更新时间
 */
class PayConfig extends Model
{
    protected ?string $table = 'pay_config';

    protected array $fillable = [
        'name','config','channel','created_at','updated_at'
    ];

    protected array $casts = [
        'name'  =>  'string',
        'config'    =>  'array',
        'channel'   =>  Channel::class,
        'created_at'    =>  'datetime',
        'updated_at'    =>  'datetime'
    ];

    public static function toPayConfig(): array
    {
        $result = [];
        $data = self::query()->lazy(10);
        foreach ($data as $value){
            /**
             * @var self $value
             */
            $result[$value->channel->name][$value->name] = $value->config;
        }
        return $result;
    }
}