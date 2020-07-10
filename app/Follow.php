<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = [
        'user_id',
        'twitter_id'
    ];

    public function User()
    {
        return $this->belongsTo('App\User');
    }

    public function twUser()
    {
        return $this->belongsTo('App\TwitterUser');
    }
}
