<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnfollowToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('unfollow_limit_count')->nullable()->comment('フォロー解除用：1日で短期間のフォロー解除を行わないようにする（1日に30人）');
            $table->string('unfollow_limit_release_time')->nullable()->comment('フォロー解除用：リクエスト制限の解除時刻を格納');
            $table->string('person_follow_limit_count')->nullable()->comment('個別フォロー用：1日で短期間のフォローを行わないようにする（1日に30人）');
            $table->string('person_follow_release_time')->nullable()->comment('個別フォロー用：リクエスト制限の解除時刻を格納');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('unfollow_limit_count');  //カラムの削除
            $table->dropColumn('unfollow_limit_release_time');  //カラムの削除
            $table->dropColumn('person_follow_limit_count');  //カラムの削除
            $table->dropColumn('person_follow_release_time');  //カラムの削除
        });
    }
}
