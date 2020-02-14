<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlackNonce extends Model
{
    //region Protected Attributes

    protected $fillable = [
        'nonce', 'slack_user_id'
    ];

    //endregion
}
