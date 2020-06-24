<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContTweet extends Model
{
    protected $fillable = [
        'coin_prices_id',
        'hour',
        'day',
        'week'
    ];

    public function coin_price()
    {
        return $this->belongdTo('App\CoinPrice');
    }
}
