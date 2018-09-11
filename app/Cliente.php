<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'codigo', 'cedula', 'nombre', 'apellido', 'telefono',
    ];

    public function envios()
    {
        return $this->hasMany('App\Envio');
    }
    public function administradores()
    {
        return $this->hasMany('App\Administrador');
    }

}
