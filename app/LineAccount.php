<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineAccount extends Model
{
    //
    protected $table = 'line_accounts';
    
    protected $fillable = [
        'mid',
        'user_id',
        'displayName',
        'pictureUrl',
        'statusMessage',
        'access_token',
        'token_type',
        'expires_in',
        'refresh_token',
        'scope'
    ];
}
