<?php

namespace App\Console\Commands;

use App\SystemManager;
use Carbon\Carbon; // ★追記
use Illuminate\Console\Command;

class ClearFollowCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clearcounter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'フォローに関する制限を解除するコマンドです';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        // システム全体でのフォロー上限数をリセットする
        $SystemManager = SystemManager::where('id', 1)->first();
        
        $now = Carbon::now();
        
        $limit = $SystemManager->one_day_system_follow_limit_time;

        // 現在の時刻よりDBの時刻のほうが15分進んでいたら処理を行う
        if($now > $limit){
            // dd('15分経過したよ');
            $SystemManager->one_day_system_follow_limit_time = Carbon::now();
            $SystemManager->save();
            exit();
        }else{
            dd('まだ15分経過していない');
        }
            
        
        
        // $time = new Carbon('+15 minutes'); // 15分後の値を取得

        // 15分経過していなかったら処理を行わない

        
        $SystemManager->one_day_system_follow_limit_time = new Carbon('+15 minutes');
        $SystemManager->one_day_system_counter = 0;
        
        $SystemManager->save();
        
        // ユーザー単位でフォロー上限数をリセットする
        


        // ユーザー単位でフォロー解除上限数をリセットする
    }
}
