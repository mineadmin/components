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

class CreateCrontabExecuteLog extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crontab_execute_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('crontab_id');
            $table->tinyInteger('status')->default(0);
            $table->string('output')->default(0);
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crontab_execute_log');
    }
}
