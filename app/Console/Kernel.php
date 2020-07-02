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
    // コマンドを登録
    Commands\GetCoinsTweet::class,
  ];

  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    // $schedule->command('command:getcoin week')
    //           ->everyThirtyMinutes();

    // $schedule->call(function () {
    //     logger()->info('クロージャーを使ってCronを動作させています');
    // });

    // $schedule
    //   ->call(function () {
    //     TwitterController::userList();
    //   })
    //   // 夜中の12に時に処理が走るメソッド
    //   // ->daily();
    //   // 5分後に処理が走るメソッド
    //   // ->everyFiveMinutes();
    //   // 毎分に処理が走るメソッド
    //   // ->everyMinute();
    //   // 15分毎に処理が走るメソッド
    //   ->everyThirtyMinutes();

    // $schedule
    //   ->call(function () {
    //     TwitterController::userList();
    //   })
    //   // 夜中の12に時に処理が走るメソッド
    //   // ->daily();
    //   // 5分後に処理が走るメソッド
    //   // ->everyFiveMinutes();
    //   // 毎分に処理が走るメソッド
    //   // ->everyMinute();
    //   // 15分毎に処理が走るメソッド
    //   ->everyThirtyMinutes();
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
