<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Twuser extends Model
{
    protected $fillable = [
        "id",
        "user_name",
        "account_name",
        "new_tweet",
        "description",
        "friends_count",
        "followers_count",
    ];

    // IDなどの数値も文字列に変換してしてVue側に渡す
    // JS側で桁数の多い Twitter_id が丸め込まれないようにする
    public $incrementing = false;
    /**
     * リレーションシップ followテーブル
     *
     */
    public function follows()
    {
        return $this->hasMany("App\Follow");
    }
}
