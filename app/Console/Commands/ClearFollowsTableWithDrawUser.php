<?php

namespace App\Console\Commands;

use App\Follow;
use Illuminate\Console\Command;

class ClearFollowsTableWithDrawUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "command:clearwithdraw";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "followsテーブルの退会済のユーザーを削除するコマンドです";

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
    public function handle(Follow $follow)
    {
        try {
            $count = count($follow->all()->where("delete_flg", 1));
            // delete_flgが立っているユーザーを削除
            $follow->where("delete_flg", 1)->delete();

            \Log::debug(
                "退会したユーザー関連の情報を削除しました。削除したレコード数：" .
                    $count
            );
            \Log::debug("   ");
        } catch (\Exception $e) {
            \Log::debug(
                "例外が発生しました。処理を停止します。  " . $e->getMessage()
            );
            exit();
        }
    }
}
