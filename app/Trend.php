<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    protected $fillable = [
        'coin_id',
        'hour',
        'day',
        'week'
    ];

    public function coin()
    {
        return $this->belongdTo('App\Coin');
    }
}
