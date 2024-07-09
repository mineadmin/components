<?php

namespace Plugin\MineAdmin\PayGateway\Request;

use Hyperf\Validation\Rule;
use Mine\MineFormRequest;
use Plugin\MineAdmin\PayGateway\Model\Enums\PayConfig\Channel;


class CrudRequest extends MineFormRequest
{
    public function indexRules(): array
    {
        return [
            'name'  =>  ['string'],
            'channel'   =>  [
                'string',Rule::enum(Channel::class)
            ],
            'created_at'    =>  [
                'array','min:2','max:2'
            ],
            'created_at.0'  =>  [
                'required_with:created_at',
                'date'
            ],
            'created_at.1'  =>  [
                'required_with:created_at',
                'date'
            ],
            'updated_at'    =>  [
                'array','min:2','max:2'
            ],
            'updated_at.0'  =>  [
                'required_with:updated_at',
                'date'
            ],
            'updated_at.1'  =>  [
                'required_with:created_at',
                'date'
            ],
        ];
    }

    public function indexAttributes(): array
    {
        return [
            'name'  =>  '通道配置名称',
            'channel'   =>  '通道类型',
            'created_at'    =>  '创建时间',
            'updated_at'    =>  '更新时间'
        ];
    }

    public function createRules(): array
    {
        return [
            'name'  =>  [
                'required','string'
            ],
            'channel'   =>  [
                'required','string',Rule::enum(Channel::class)
            ],
            'config'    =>  [
                'required','array','min:1'
            ]
        ];
    }

    public function createAttributes(): array
    {
        return [
            'name'  =>  '通道名称',
            'channel'   =>  '通道类型',
            'config'    =>  '通道配置项'
        ];
    }

    public function saveRules(): array
    {
        return [
            'id'    => [
                'required','integer',Rule::exists('pay_config','id')
            ],
            'name'  =>  [
                'required','string'
            ],
            'channel'   =>  [
                'required','string',Rule::enum(Channel::class)
            ],
            'config'    =>  [
                'required','array','min:1'
            ]
        ];
    }

    public function saveAttributes(): array
    {
        return [
            'name'  =>  '通道名称',
            'channel'   =>  '通道类型',
            'config'    =>  '通道配置项'
        ];
    }

    public function deleteRules(): array
    {
        return [
            'ids'   =>  [
                'required','array','min:1'
            ],
            'ids.*' =>  [
                'required','integer',Rule::exists('pay_config','id')
            ]
        ];
    }

    public function setGlobalSettingRules(): array
    {
        return [
            'logger'    =>  [
                'required','array'
            ],
            'logger.enable'    =>  [
                'required','bool'
            ],
            'http'  =>  [
                'required','array'
            ],
            'http.timeout'  =>  [
                'required'
            ],
            'http.connect_timeout' => [
                'required'
            ]
        ];
    }

    public function uploadRules(): array
    {
        return [
            'file'  =>  [
                'required','file'
            ]
        ];
    }
}