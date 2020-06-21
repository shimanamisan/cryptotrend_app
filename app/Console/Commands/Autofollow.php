<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Autofollow extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'autofollow';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  // 自動フォローのステータス
  const AUTO_FOLLOW_STATUS_RUN = 1;

  // フォロー制限
  const DAY_FOLLOW_LIMIT = 1000;

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    Log::info('=====================================================================');
    Log::info('AutoFollow : 開始');
    Log::info('=====================================================================');

    Log::info('=====================================================================');
    Log::info('AutoFollow : 終了');
    Log::info('=====================================================================');
  }

  // バッチ処理を実行し、リクエストの上限を超えないようにフォローを繰り返す
  public function autoFollow()
  {
    //

    $auto_follow_status_run = User::where('autofollow', AUTO_FOLLOW_STATUS_RUN)->get();
    Log::debug();
  }

  public function fetchAutoFollow($twitter_user, $user_id)
  {
    //
  }
  public function TwitterOAuth()
  {
    //
  }
}
