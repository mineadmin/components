<?php

namespace Plugin\MineAdmin\PayGateway;

use App\System\Model\SystemMenu;

class InstallScript {

    public function __invoke(){
        $parent = SystemMenu::create([
            'parent_id' => 0,
            'level' => '0',
            'name' => '支付配置',
            'code' => 'plugin:mineadmin:pay_gateway',
            'icon' => '',
            'route' => 'plugin/mineadmin/pay_gateway',
            'component' => 'plugin/mineadmin/payConfig',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'M',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);
        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '支付配置列表',
            'code' => 'plugin:mineadmin:pay_gateway:index',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);

        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '支付配置新增',
            'code' => 'plugin:mineadmin:pay_gateway:create',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);

        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '支付配置修改',
            'code' => 'plugin:mineadmin:pay_gateway:save',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);


        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '支付配置删除',
            'code' => 'plugin:mineadmin:pay_gateway:delete',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);


        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '获取全局配置项',
            'code' => 'plugin:mineadmin:pay_gateway:get_global_setting',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);
        SystemMenu::create([
            'parent_id' => $parent->id,
            'level' => '0',
            'name' => '设置全局配置项',
            'code' => 'plugin:mineadmin:pay_gateway:set_global_setting',
            'icon' => '',
            'route' => '',
            'component' => '',
            'redirect' => '',
            'is_hidden' => '2',
            'type' => 'B',
            'status' => 1,
            'sort' => 99,
            'created_by' => 0,
            'updated_by' => 0,
            'deleted_at' => null,
            'remark' => '',
        ]);
    }

}