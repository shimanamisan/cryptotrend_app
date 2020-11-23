<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable = ["coin_id", "tweet"];

    protected $casts = [
        "tweet" => "integer",
    ];

    public function coin()
    {
        return $this->belongdTo("App\Coin");
    }
}
