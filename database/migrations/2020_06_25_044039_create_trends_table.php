<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('trends', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('coin_id')->unsigned();
        //     $table->string('hour')->nullable();
        //     $table->string('day')->nullable();
        //     $table->string('week')->nullable();
        //     $table->foreign('coin_id')->references('id')->on('coins');

        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('trends');
    }
}
