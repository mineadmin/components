<?php

namespace Mine\NextCoreX;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations'    =>  [
                'scan'  =>  [
                    'paths'  =>  [
                        __DIR__
                    ]
                ]
            ]
        ];
    }
}