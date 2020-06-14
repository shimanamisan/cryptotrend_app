<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterUser extends Model
{
    protected $fillable = [
        'twitter_id',
        'user_name',
        'account_name',
        'new_tweet',
        'description',
        'friends_count',
        'followers_count',
    ];
}
