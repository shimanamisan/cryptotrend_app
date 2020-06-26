<?php

use Carbon\Carbon; // ★追記
use Illuminate\Database\Seeder;

class CoinPriceSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coin_name = [
            'ビットコイン',
            'イーサリアム',
            'イーサリアムクラシック',
            'リスク',
            'ファクトム',
            'リップル',
            'ネム',
            'ライトコイン',
            'ビットコインキャッシュ',
            'モナコイン',
            'ステラルーメン',
            'クアンタム',
        ];
        
        foreach($coin_name as $coin_name_item){
            
            DB::table('coins')->insert([
                'coin_name' => $coin_name_item,
                'max_price' => 0,
                'low_price' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
