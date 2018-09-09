<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $fillable = [
        'codigo', 'cliente_id', 'fecharecogida', 'paisrecogida', 'estadorecogida', 'direccionrecogida', 'paisllegada', 'estadollegada', 'direccionllegada'
    ];

    /**
     * Obtiene las paquetes creadas por un cliente
     */
    public function paquetes()
    {
        return $this->hasMany('App\Paquete');
    }
}
