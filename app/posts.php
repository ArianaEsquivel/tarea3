<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class posts extends Model
{
    public function comentarios()
    {
        return $this->hasMany('App\comentarios');
    }
    public function User()
    {
        return $this->belongsTo('App\User');
    }
    protected $fillable = [
        'titulo', 'descripcion', 'imagen', 'user_id'
    ];
}
