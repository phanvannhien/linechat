<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $table = 'messages';
    protected $fillable = ['room_id','from_mid','to_mid','message'];
}
