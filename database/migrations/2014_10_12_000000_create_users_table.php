<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('my_twitter_id')->nullable()->comment('SNS認証した際に入る、SNS側のユーザー固有のID');
            $table->string('twitter_token')->nullable()->comment('SNS認証した際に入る、SNS側のユーザー固有のトークン');
            $table->string('twitter_token_secret')->nullable()->comment('SNS認証した際に入る、SNS側のユーザー固有のシークレットトークン');
            $table->string('follow_limit_time')->nullable()->comment('API制限がかかった際に、現在の時刻が入る');
            $table->string('follor_limit_count')->nullable()->comment('15フォロー/15分を超えないようカウントする値が入る');
            $table->boolean('autofollow_status')->default(false)->comment('自動フォローのステータス（ON/OFF）を格納');
            $table->boolean('delete_flg')->default(false);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
