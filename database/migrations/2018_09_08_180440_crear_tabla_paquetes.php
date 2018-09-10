<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaPaquetes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paquetes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('envio_id')->unsigned()->nullable();
            $table->integer('tipopaquete_id')->unsigned()->nullable();
            $table->string('peso')->nullable();
            $table->timestamps();

            $table->foreign('tipopaquete_id')
                ->references('id')->on('tipo_paquetes')
                ->onUpdate('cascade');
            $table->foreign('envio_id')
                ->references('id')->on('envios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paquetes');
    }
}
