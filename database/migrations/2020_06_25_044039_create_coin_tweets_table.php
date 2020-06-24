<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_tweets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_prices_id')->unsigned();
            $table->string('hour');
            $table->string('day');
            $table->string('week');
            $table->foreign('coin_prices_id')->references('id')->on('coin_prices');

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
        Schema::dropIfExists('coin_tweets');
    }
}
