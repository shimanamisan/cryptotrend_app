<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class ClearFollowsTableWithDrawUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clearwithdraw {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'followsテーブルの退会済のユーザーを削除するコマンドです';

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
    public function handle(User $user)
    {
        // バッチ処理で実行する際の引数を受け取る
        $user_id = $this->argument('user_id');

        try{
            // delete_flgが立っているユーザーを削除
            $user->find($user_id)->follows()->delete(['delete_flg' => 1]);

            \Log::debug('退会したユーザー関連の情報を削除しました。ユーザーID： '. $user_id);
            \Log::debug('   ');
        }catch(\Exception $e){
            \Log::debug('例外が発生しました。処理を停止します。  '. $e->getMessage());
            exit();
        }
    }
}
