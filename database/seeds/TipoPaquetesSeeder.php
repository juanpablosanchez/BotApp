<?php

use Illuminate\Database\Seeder;

class TipoPaquetesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\TipoPaquete::create(['nombre' => 'Documentos']);
        App\TipoPaquete::create(['nombre' => 'Caja pequeña']);
        App\TipoPaquete::create(['nombre' => 'Caja mediana']);
        App\TipoPaquete::create(['nombre' => 'Caja grande']);
    }
}
