<?php

use Illuminate\Database\Seeder;

class EstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Estado::create(['nombre' => 'Pendiente']);
        App\Estado::create(['nombre' => 'Recogido']);
        App\Estado::create(['nombre' => 'En camino']);
        App\Estado::create(['nombre' => 'Entregado']);
    }
}
