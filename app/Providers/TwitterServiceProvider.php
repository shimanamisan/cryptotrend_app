<?php

namespace App\Providers;

use Abraham\TwitterOAuth\TwitterOAuth; // ★追記
use Illuminate\Support\ServiceProvider;

class TwitterServiceProvider extends ServiceProvider
{
  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = true;

  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->singleton('twitter', function () {
      // ヘルパー関数のconfigメソッドを通じて、config/twitter.phpの中身を参照
      $config = config('twitter');
      // 認証に必要な設定を読み込んでインスタンスを生成、返却している
      return new TwitterOAuth($config['client_id'], $config['client_secret'], $config['access_token'], $config['access_token_secret']);
    });
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return ['twitter'];
  }
}
