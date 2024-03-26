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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateCrontab extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crontab', function (Blueprint $table) {
            $table->bigIncrements('id');
            /*
             * name string 30
             * status tinyint 1 default 0
             * memo string 60 default null
             * type string 10 not null
             * value string longtext not null
             */
            $table->string('name', 30);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_on_one_server')->default(0);
            $table->tinyInteger('is_singleton')->default(0);
            $table->string('memo', 60)->default(null);
            $table->string('type', 10);
            $table->string('rule', 10);
            $table->text('value');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crontab');
    }
}
