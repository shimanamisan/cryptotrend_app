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

  ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 過去1時間の各銘柄に関するツイートを取得
        $schedule->command('command:getcoin hour')
        // 1時間おきに実行する
        ->hourly()->everyThirtyMinutes();

        // 過去1日の各銘柄に関するツイートを取得
        $schedule->command('command:getcoin day')
        // 毎日深夜12時に実行する
        ->daily()->everyThirtyMinutes();

        // 過去1週間の各銘柄に関するツイートを取得
        $schedule->command('command:getcoin week')
        // 毎日深夜3時に実行する
        ->dailyAt('3:00')->everyThirtyMinutes();

        // CoincheckAPIからビットコインの価格を取得する
        $schedule->command('command:getticker')
        // 30分毎に実行する
        ->everyThirtyMinutes();

        // 仮想通貨関連のアカウント情報を取得する
        $schedule->command('command:gettwitterusers')
        // 1日1回、深夜1時に実行する
        ->dailyAt('1:00')->withoutOverlapping();

        // 自動フォローを行う処理
        $schedule->command('command:autofollow')
        // 30分毎に処理を実行し、前の処理が終わっていない（多重起動）場合は処理を実行しない
        ->everyThirtyMinutes()->withoutOverlapping();
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
