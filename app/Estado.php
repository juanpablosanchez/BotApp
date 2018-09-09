<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $fillable = [
        'nombre',
    ];

    /**
     * Obtiene las envios creadas por un cliente
     */
    public function envios()
    {
        return $this->hasMany('App\Envio');
    }
}
