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
            $table->string('unfollow_limit_count')->nullable()->comment('フォロー解除用：15フォロー/15分を超えないようカウントする値が入る');
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
            $table->timestamp('unfollow_limit_time')->nullable()->comment('フォロー解除用：API制限がかかった際に、現在の時刻が入る');
            $table->string('unfollow_limit_count')->nullable()->comment('フォロー解除用：15フォロー/15分を超えないようカウントする値が入る');
        });
    }
}
