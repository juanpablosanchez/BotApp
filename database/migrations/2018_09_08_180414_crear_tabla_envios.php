<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaEnvios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('estado_id');
            $table->string('codigo', 20);
            $table->string('fecharecogida', 11);
            $table->string('paisrecogida', 30);
            $table->string('estadorecogida', 30);
            $table->string('direccionrecogida', 50);
            $table->string('paisllegada', 30);
            $table->string('estadollegada', 30);
            $table->string('direccionllegada', 50);
            $table->timestamps();

            $table->foreign('cliente_id')
                ->references('id')->on('clientes')
                ->onUpdate('cascade');
            $table->foreign('estado_id')
                ->references('id')->on('estados')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('envios');
    }
}
