<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Exception;

class PackTypeAdminRemoveConversacion extends Conversation
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
        $this->requestPackTypeToDelete();
    }

    public function requestPackTypeToDelete()
    {
        $this->ask("Ingrese el número del tipo de paquete que desea eliminar", function (Answer $answer) {
            $number = $answer->getText();

            if ($this->isIntoRange($number, 1, $this->packsType->count())) {
                $deleted = $this->deletePackType($number);

                if ($deleted) {
                    $this->showPacksTypeList();
                } else {
                    $this->say('El número de tipo de paquete ingresado  se encuentra asociado a un paquete registrado');
                }
            } else {
                $this->ErrorRequestPackTypeToDelete();
            }
        });
    }

    public function ErrorRequestPackTypeToDelete()
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
                $this->requestPackTypeToDelete();
            }
        });
    }

    public function deletePackType($number)
    {
        $id = $this->packsType[$number - 1]->id;

        try {
            \App\TipoPaquete::where('id', $id)->delete();
            return true;
        } catch (Exception $e) {
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
