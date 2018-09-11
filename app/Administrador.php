<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    protected $fillable = [
        'cliente_id'
    ];

    public function cliente_id()
    {
        return $this->hasMany('App\Cliente');
    }
}
