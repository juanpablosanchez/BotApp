<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $fillable = [
        'tipopaquete_id', 'envio_id', 'peso',
    ];
}
