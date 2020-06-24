<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoinPrice extends Model
{
    protected $fillable = [
        'coin_name',
        'max_price',
        'low_price'
    ];
    
    public function coin_tweets()
    {
        return $this->hasMany('App\CoinTweet');
    }
}
