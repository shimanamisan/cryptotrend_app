<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twusers', function (Blueprint $table) {
            $table->string('id')->primary()->comment('twitter_idが入るカラム');
            $table->string('user_name');
            $table->string('account_name');
            $table->string('new_tweet');
            $table->string('description');
            $table->string('friends_count');
            $table->string('followers_count');
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
        Schema::dropIfExists('twusers');
    }
}
