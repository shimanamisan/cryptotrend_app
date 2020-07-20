<?php

namespace App\Console\Commands;

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

        $SystemManager->one_day_system_counter = 0;

        $SystemManager->save();

        // ユーザー単位でフォロー上限数をリセットする
        


        // ユーザー単位でフォロー解除上限数をリセットする
    }
}
