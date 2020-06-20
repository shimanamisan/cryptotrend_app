<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Twitter extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    // twitterというキーを返して、サービスコンテナ（DIコンテナ）に登録したインスタンスを実体化する
    return 'twitter';
  }
}
