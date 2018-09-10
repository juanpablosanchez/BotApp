<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class PackTypeAdminEditConversacion extends Conversation
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
        $this->showPacksTypeList();
        $this->requestPackTypeToEdit();
    }

    public function requestPackTypeToEdit()
    {
        $this->ask("Ingrese el número del tipo de paquete que desea eliminar", function (Answer $answer) {
            $number = $answer->getText();

            if ($this->isIntoRange($number, 1, $this->packsType->count())) {
                $this->packTypeToUpdate = $this->packsType[intval($number) - 1];
                $this->requestNewPackType();
            } else {
                $this->ErrorRequestPackTypeToEdit();
            }
        });
    }

    public function requestNewPackType()
    {
        $this->ask("Ingrese el nuevo tipo de paquete", function (Answer $answer) {
            $newPackType = $answer->getText();
            $updated = $this->updatePackType($newPackType);

            if ($updated) {
                $this->showPacksTypeList();
            } else {
                $this->say('El tipo de paquete "' . $newPackType . '" existe');
            }
        });
    }

    public function ErrorRequestPackTypeToEdit()
    {
        $message = Question::create('El número del tipo de paquete ingresado no existe. ¿Desea volver a ver la lista de tipos de paquetes registrados?')
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
                $this->requestPackTypeToEdit();
            }
        });
    }

    public function updatePackType($newPackType)
    {
        $packsTypeByName = \App\TipoPaquete::where('nombre', $newPackType)->get();

        if($packsTypeByName->isEmpty()) {
            \App\TipoPaquete::where('id', $this->packTypeToUpdate->id)->update([
                'nombre' => trim($newPackType),
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function showPacksTypeList()
    {
        $this->packsType = \App\TipoPaquete::orderBy('nombre', 'asc')->get();
        $index = 0;

        $this->say('Tipo de paquetes registrados');
        foreach ($this->packsType as $packType) {
            $index++;
            $this->say($index . '. ' . $packType->nombre);
        }
    }

    public function isIntoRange($number, $min, $max)
    {
        $formattedNumber = intval($number);
        return ($formattedNumber > 0 && $formattedNumber >= $min && $formattedNumber <= $max);
    }
}
