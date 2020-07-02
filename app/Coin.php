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
    
    public function hours()
    {
        return $this->hasMany('App\Hour');
    }
    
    public function days()
    {
        return $this->hasMany('App\Day');
    }
    
    public function weeks()
    {
        return $this->hasMany('App\Week');
    }
}
