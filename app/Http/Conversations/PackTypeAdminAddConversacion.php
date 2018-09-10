<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class PackTypeAdminAddConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->showCurrentPacksType();
    }

    public function showCurrentPacksType()
    {
        $packsType = \App\TipoPaquete::orderBy('nombre', 'asc')->get();
        $index = 0;

        foreach ($packsType as $packType) {
            $index++;
            $this->say($index . '. ' . $packType->nombre);
        }

        $this->requestNewPackType();
    }

    public function requestNewPackType()
    {
        $this->ask("Ingrese el nuevo tipo de paquete", function (Answer $answer) {
            $packType = $answer->getText();

            if ($this->existPackType($packType)) {
                $this->errorRequestNewPackType();
            } else {
                $this->registerNewPackType($packType);
                $this->say('El nuevo paquete ha sido registrado correctamente');
            }
        });
    }

    public function errorRequestNewPackType()
    {
        $message = Question::create('El tipo de paquete ingresado ya existe. ¿Desea ingresar uno nuevo?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::OK) {
                    $this->showCurrentPacksType();
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->requestNewPackType();
            }
        });
    }

    public function existPackType($packType)
    {
        return \App\TipoPaquete::where('nombre', trim($packType))->count() > 0;
    }

    public function registerNewPackType($packType)
    {
        return \App\TipoPaquete::create([
            'nombre' => trim($packType),
        ]);
    }
}
