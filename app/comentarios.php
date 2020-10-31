<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comentarios extends Model
{
    public function posts()
    {
        return $this->belongsTo('App\Posts');
    }
    public function User()
    {
        return $this->belongsTo('App\User');
    }
    protected $fillable = [
        'comentario', 'post_id', 'user_id'
    ];
}
