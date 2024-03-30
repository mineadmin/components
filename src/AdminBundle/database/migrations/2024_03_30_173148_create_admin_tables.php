<?php


use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use function Hyperf\Config\config;

class CreateAdminTables extends Migration
{
    public function getConnection (): string
    {
        return $this->config('database.connection') ?: config('database.default');
    }

    public function config(string $key): mixed
    {
        return config('admin.bundle.' . $key);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->config('database.users_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigIncrements('id');
            $table->string('username', 20)->unique();
            $table->string('password', 100);
            $table->string('user_type', 3)->default(100);
            $table->string('nickname', 30)->default('');
            $table->string('phone', 11)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('avatar', 255)->default('xxx');
            $table->string('signed', 255)->default('xxx');
            $table->string('dashboard', 100)->default('index');
            $table->bigInteger('dept_id')->unsigned()->default(0);
            $table->tinyInteger('status')->default(1);
            $table->ipAddress('login_ip')->default('127.0.0.1');
            $table->string('remark', 255)->default('');
            $table->timestamp('last_login_at');
            $table->json('backend_setting')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create($this->config('database.role_table'), function (Blueprint $table) {
            Schema::create('system_role', function (Blueprint $table) {
                $table->engine = 'Innodb';
                $table->comment('角色信息表');
                $table->bigIncrements('id')->comment('主键');
                $table->addColumn('string', 'name', ['length' => 30, 'comment' => '角色名称']);
                $table->addColumn('string', 'code', ['length' => 100, 'comment' => '角色代码']);
                $table->addColumn(
                    'smallInteger',
                    'data_scope', [
                        'length' => 1,
                        'default' => 1,
                        'comment' => '数据范围（1：全部数据权限 2：自定义数据权限 3：本部门数据权限 4：本部门及以下数据权限 5：本人数据权限）',
                    ]
                )->nullable();
                $table->addColumn('smallInteger', 'status', ['default' => 1, 'comment' => '状态 (1正常 2停用)'])->nullable();
                $table->addColumn('smallInteger', 'sort', ['unsigned' => true, 'default' => 0, 'comment' => '排序'])->nullable();
                $table->addColumn('string', 'remark', ['length' => 255, 'comment' => '备注'])->nullable();
                $table->addColumn('bigInteger', 'created_by', ['comment' => '创建者'])->default(0);
                $table->addColumn('bigInteger', 'updated_by', ['comment' => '更新者'])->default(0);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
            });
        });

        Schema::create($this->config('database.menu_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->addColumn('bigInteger', 'parent_id', ['unsigned' => true]);
            $table->addColumn('string', 'level', ['length' => 500, ]);
            $table->addColumn('string', 'name', ['length' => 50, ]);
            $table->addColumn('string', 'code', ['length' => 100, ]);
            $table->addColumn('string', 'icon', ['length' => 50, ])->nullable();
            $table->addColumn('string', 'route', ['length' => 200, ])->nullable();
            $table->addColumn('string', 'component', ['length' => 255, ])->nullable();
            $table->addColumn('string', 'redirect', ['length' => 255, ])->nullable();
            $table->tinyInteger('is_hidden')->default(1);
            $table->char('type',1)->default('');
            $table->smallInteger('status')->default(1);
            $table->tinyInteger('sort')->unsigned()->default(0);
            $table->addColumn('bigInteger', 'created_by', [])->default(0);
            $table->addColumn('bigInteger', 'updated_by', [])->default(0);
            $table->timestamps();
            $table->addColumn('timestamp', 'deleted_at', ['precision' => 0, ])->nullable();
            $table->addColumn('string', 'remark', ['length' => 255, ])->nullable();
        });

        Schema::create($this->config('database.department_table'), function (Blueprint $table) {
            $table->comment('部门信息表');
            $table->bigIncrements('id')->comment('主键');
            $table->addColumn('bigInteger', 'parent_id', ['unsigned' => true, 'comment' => '父ID']);
            $table->addColumn('string', 'level', ['length' => 500, 'comment' => '组级集合']);
            $table->addColumn('string', 'name', ['length' => 30, 'comment' => '部门名称']);
            $table->addColumn('string', 'leader', ['length' => 20, 'comment' => '负责人'])->nullable();
            $table->addColumn('string', 'phone', ['length' => 11, 'comment' => '联系电话'])->nullable();
            $table->addColumn('smallInteger', 'status', ['default' => 1, 'comment' => '状态 (1正常 2停用)'])->nullable();
            $table->addColumn('smallInteger', 'sort', ['unsigned' => true, 'default' => 0, 'comment' => '排序'])->nullable();
            $table->addColumn('bigInteger', 'created_by', ['comment' => '创建者'])->nullable();
            $table->addColumn('bigInteger', 'updated_by', ['comment' => '更新者'])->nullable();
            $table->addColumn('timestamp', 'created_at', ['precision' => 0, 'comment' => '创建时间'])->nullable();
            $table->addColumn('timestamp', 'updated_at', ['precision' => 0, 'comment' => '更新时间'])->nullable();
            $table->addColumn('timestamp', 'deleted_at', ['precision' => 0, 'comment' => '删除时间'])->nullable();
            $table->addColumn('string', 'remark', ['length' => 255, 'comment' => '备注'])->nullable();
            $table->index('parent_id');
        });

        Schema::create($this->config('database.department_user_table'), function (Blueprint $table) {
            $table->comment('用户与部门关联表');
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true, 'comment' => '用户主键']);
            $table->addColumn('bigInteger', 'dept_id', ['unsigned' => true, 'comment' => '部门主键']);
            $table->primary(['user_id', 'dept_id']);
        });

        Schema::create($this->config('database.department_leader_table'), function (Blueprint $table) {
            $table->comment('部门领导表');
            $table->addColumn('bigInteger', 'dept_id', ['unsigned' => true, 'comment' => '部门主键']);
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true, 'comment' => '用户主键']);
            $table->addColumn('string', 'username', ['length' => 20, 'comment' => '用户名']);
            $table->timestamp('created_at')->comment('添加时间');
            $table->primary(['dept_id', 'user_id']);
        });

        Schema::create($this->config('database.user_role'), function (Blueprint $table) {
            $table->comment('用户与角色关联表');
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true, 'comment' => '用户主键']);
            $table->addColumn('bigInteger', 'role_id', ['unsigned' => true, 'comment' => '角色主键']);
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create($this->config('database.role_menu_table'), function (Blueprint $table) {
            $table->comment('角色与菜单关联表');
            $table->addColumn('bigInteger', 'role_id', ['unsigned' => true, 'comment' => '角色主键']);
            $table->addColumn('bigInteger', 'menu_id', ['unsigned' => true, 'comment' => '菜单主键']);
            $table->primary(['role_id', 'menu_id']);
        });

        Schema::create($this->config('database.role_menu_table'), function (Blueprint $table) {
            $table->comment('角色与菜单关联表');
            $table->addColumn('bigInteger', 'role_id', ['unsigned' => true, 'comment' => '角色主键']);
            $table->addColumn('bigInteger', 'menu_id', ['unsigned' => true, 'comment' => '菜单主键']);
            $table->primary(['role_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->config('database.users_table'));
        Schema::dropIfExists($this->config('database.menu_table'));
        Schema::dropIfExists($this->config('database.role_table'));
        Schema::dropIfExists($this->config('database.department_table'));
        Schema::dropIfExists($this->config('database.department_user_table'));
        Schema::dropIfExists($this->config('database.department_leader_table'));
        Schema::dropIfExists($this->config('database.role_menu_table'));
    }
}