<?php

namespace App\Console;

use App\Http\Controllers\TwitterController; // ★追加
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // $schedule->call(function () {
        //     logger()->info('クロージャーを使ってCronを動作させています');
        // });

        $schedule
            ->call(function () {
                TwitterController::userList();
            })
            // 夜中の12に時に処理が走るメソッド
            ->daily();
        // 5分後に処理が走るメソッド
            // ->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
