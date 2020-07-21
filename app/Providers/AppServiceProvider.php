<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider; // 追加

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        # 商用環境以外だった場合、SQLログを出力させます
        if (config('app.env') !== 'production') {
            \DB::listen(function ($query) {
                \Log::info("Query Time:{$query->time}s] $query->sql");
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // マイグレーションにより生成されるデフォルトのインデックス用文字列長を
        // 明示的に設定する必要がる（MySQLは5.7未満で必要な設定）
        Schema::defaultStringLength(191);

        /**
         * .envファイルの(APP_ENV=production)のとき、強制https化
         */
        if(\App::environment('production')) {
            \URL::forceScheme('https');
        }
    }
}
