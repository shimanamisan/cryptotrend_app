<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_managers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('one_day_system_counter')->comment('1000/日を超えないようにカウントしていく');
            $table->timestamp('one_day_system_follow_release_time')->nullable()->comment('アプリ単位でリクエスト制限制限を解除する判定の時刻が入る');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_managers');
    }
}
