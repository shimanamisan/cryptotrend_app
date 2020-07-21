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
            $table->string('one_day_system_counter');
            $table->timestamp('one_day_system_follow_limit_time')->nullable()->comment('アプリ単位でフォロー上限の制限をかける際に、現在の時刻が入る');
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
