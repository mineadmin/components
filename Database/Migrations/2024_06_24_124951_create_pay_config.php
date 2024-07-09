<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePayConfig extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pay_config', function (Blueprint $table) {
            $table->comment('支付相关配置');
            $table->bigIncrements('id');
            $table->string('channel',10)->comment('支付通道 alipay,wechat,unipay,jsb');
            $table->string('name',20)->default('default')->comment('配置项名称');
            $table->json('config')->comment('配置项');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_config');
    }
}
