<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('twuser_id');
            $table->boolean('delete_flg')->default(false);
            $table->timestamps();

            // users削除されたときに、それに関連するfollowの情報も削除される
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // twusersが削除されたときに、それに関連するfollowの情報も削除される
            $table->foreign('twuser_id')->references('id')->on('twusers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('follows');
    }
}
