<?php

namespace App\Console\Commands;

use App\Coin; // ★追記
use Illuminate\Console\Command;

class GetTicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getticker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CoincheckAPIから24時間の取引価格を取得します';

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
    public function handle(Coin $coin)
    {
        \Log::debug('ビットコインの取引価格を取得するバッチ処理が実行されています');
        \Log::debug('    ');
        // 現在はビットコインのみ24時間の取引価格を取得
        $result = file_get_contents('https://coincheck.com/api/ticker');

        $ticker = json_decode($result);
        try {
            $coins = $coin->find(1);
            
            $coins->max_price = $ticker->high;
    
            $coins->low_price = $ticker->low;
    
            $coins->save();
        } catch (\Exception $e) {
            \Log::debug('例外が発生しました。処理を停止します。' . $e->getMessage());
        }
    }
}
