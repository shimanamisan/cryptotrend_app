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
            $table->timestamp('unfollow_limit_time')->nullable()->comment('フォロー解除用：API制限がかかった際に、現在の時刻が入る');
            $table->string('unfollow_limit_count')->nullable()->comment('フォロー解除用：1日で短期間のフォロー解除を行わないようにする（1日に20人）');
            $table->string('person_follow_limit_count')->nullable()->comment('個別フォロー時、1日で短期間のフォローを行わないようにする（1日に20人）');
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
            $table->dropColumn('unfollow_limit_time');  //カラムの削除
            $table->dropColumn('unfollow_limit_count');  //カラムの削除
            $table->dropColumn('person_follow_limit_count');  //カラムの削除
        });
    }
}
