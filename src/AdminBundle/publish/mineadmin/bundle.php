<?php

use Mine\Admin\Bundle\Model\MenuModel;
use Mine\Admin\Bundle\Model\PostModel;
use Mine\Admin\Bundle\Model\RoleModel;
use Mine\Admin\Bundle\Model\UserModel;

return [
    'database'  =>  [
        // Database connection for following tables.
        'connection'    =>  null,
        // 用户表
        'users_table'   =>  'users',
        'users_model'   => UserModel::class,

        // 菜单表
        'menu_table'    =>  'menu',
        'menu_model'     => MenuModel::class,

        // 角色表名
        'role_table'    =>  'roles',
        'role_model'    => RoleModel::class,

        'post_table'   => 'post',
        'post_model'  =>  PostModel::class,

        // 部门表
        'department_table'    => 'department',
        // 部门与用户关系表
        'department_user_table' => 'department_user',
        // 部门与领导关系表
        'department_leader_table' => 'department_leader',

        // 角色与用户关系表
        'user_role'     =>  'user_role',
        // 角色与菜单关系表
        'role_menu_table' => 'role_menu',
        // 用户与岗位关系表
        'user_post_table'   =>  'user_post',
    ]
];