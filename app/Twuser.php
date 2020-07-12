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

    // IDなどの数値も文字列化してVue側に渡す
    // JS側がbigintではなくNumberとして解釈するのが原因のようだ
    public $incrementing = false;
    /**
     * リレーションシップ followテーブル
     * 
     */
    public function follows()
    {
        return $this->hasMany('App\Follow');
    }

}
