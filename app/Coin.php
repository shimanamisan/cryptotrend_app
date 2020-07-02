<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'coin_name',
        'max_price',
        'low_price'
    ];
    
    public function trends()
    {
        return $this->hasMany('App\Trend');
    }
}
