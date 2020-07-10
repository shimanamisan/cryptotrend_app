<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Twuser extends Model
{
    protected $fillable = [
        'id',
        'user_name',
        'account_name',
        'new_tweet',
        'description',
        'friends_count',
        'followers_count',
    ];

    /**
     * リレーションシップ followテーブル
     * 
     */
    public function follows()
    {
        return $this->hasMany('App\Follow');
    }
}
