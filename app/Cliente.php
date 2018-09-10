<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'codigo', 'cedula', 'nombre', 'apellido', 'telefono',
    ];

    /**
     * Obtiene las citas creadas por un cliente
     */
    public function envios()
    {
        return $this->hasMany('App\Envio');
    }
    /**
     * Obtiene las citas creadas por un cliente
     */
    public function administradores()
    {
        return $this->hasMany('App\Administrador');
    }

}
