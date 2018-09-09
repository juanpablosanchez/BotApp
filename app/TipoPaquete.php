<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoPaquete extends Model
{
    protected $fillable = [
        'nombre',
    ];

    /**
     * Obtiene las paquetes creadas por un cliente
     */
    public function paquetes()
    {
        return $this->hasMany('App\Paquete');
    }
}
