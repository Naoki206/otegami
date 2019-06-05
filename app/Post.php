<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'text', 'reply_id', 'reply_flg', 'destination_id'
		];

    public function user() {
        return $this->belongsTo('App\User');
    }
    use SoftDeletes;
    protected $dates = ['deleted_at'];

}
