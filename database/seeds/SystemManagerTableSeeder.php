<?php

use Carbon\Carbon; // ★追記
use Illuminate\Database\Seeder;

class SystemManagerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_managers')->insert([
            'one_day_system_counter' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
