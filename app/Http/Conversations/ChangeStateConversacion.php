<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use App\Http\Helper\Helper;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class ChangeStateConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->showAllSendings();
    }

    public function stopsConversation(IncomingMessage $message)
    {
        return Helper::stopConversation($message->getText());
    }

    public function showAllSendings()
    {
        $deliveredSendingState = \App\Estado::where('nombre', Constant::DELIVERED_SENDING_STATE)->get()->first();
        $this->sendings = \App\Envio::where('estado_id', '<>', $deliveredSendingState->id)->get();

        foreach ($this->sendings as $sending) {
            $user = \App\Cliente::where('id', $sending->cliente_id)->get()->first();
            $packagesCount = \App\Paquete::where('envio_id', $sending->id)->get()->count();
            $this->say('Envío ' . $sending->codigo . ' a nombre de ' . $user->nombre . ', con ' . $packagesCount . ' paquete' . ($packagesCount > 0 ? 's' : ''));
        }

        $this->requestSendingCode();
    }

    public function requestSendingCode()
    {
        $this->ask("Ingrese el código del envío", function (Answer $answer) {
            $sendingCode = trim($answer->getText());
            $sendingsFiltered = $this->sendings->where('codigo', $sendingCode);
            if ($sendingsFiltered->isEmpty()) {
                $this->say('Código inválido');
                $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
            } else {
                $this->sending = $sendingsFiltered->first();
                $user = \App\Cliente::where('id', $this->sending->cliente_id)->get()->first();
                $sendingState = \App\Estado::where('id', $this->sending->estado_id)->get()->first();

                $this->say(
                    'Código: ' . $this->sending->codigo .
                    ', Cliente: ' . $user->nombre .
                    ', Estado: ' . $sendingState->nombre .
                    ', Fecha recogida: ' . $this->sending->fecharecogida .
                    ', País recogida: ' . $this->sending->paisrecogida .
                    ', Estado recogida: ' . $this->sending->estadorecogida .
                    ', Dirección recogida: ' . $this->sending->direccionrecogida .
                    ', País llegada: ' . $this->sending->paisllegada .
                    ', Estado llegada: ' . $this->sending->estadollegada .
                    ', Dirección llegada: ' . $this->sending->direccionllegada
                );

                $this->actionToContinue();
            }
        });
    }

    public function actionToContinue()
    {
        $message = Question::create('¿Qué desea hacer?')
            ->addButtons([
                Button::create('Modificar estado')->value(Constant::EDIT),
                Button::create('Mostrar listado')->value(Constant::LIST),
                Button::create('Cancelar')->value(Constant::CANCEL),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                switch ($answer->getValue()) {
                    case Constant::EDIT:
                        $this->showAllStates();
                        break;
                    case Constant::LIST:
                        $this->showAllSendings();
                        break;
                    default:
                        $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
                        break;
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function showAllStates()
    {
        $this->sendingStates = \App\Estado::all();
        $index = 0;
        $this->say('Estados:');
        foreach ($this->sendingStates as $sendingState) {
            $index++;
            $this->say($index . '. ' . $sendingState->nombre);
        }

        $this->ask("Ingrese el número del estado a seleccionar", function (Answer $answer) {
            $state = trim($answer->getText());
            if ($this->isIntoRange($state, 1, $this->sendingStates->count())) {
                $this->updateStateOfSending($this->sendingStates[$state]);
            } else {
                $this->errorShowAllStates();
            }
        });
    }

    public function updateStateOfSending($sendingState)
    {
        \App\Envio::where('id', $this->sending->id)
            ->update(['estado_id' => $sendingState->id]);

        $sending = \App\Envio::where('id', $this->sending->id)->get()->first();
        $user = \App\Cliente::where('id', $sending->cliente_id)->get()->first();
        $sendingState = \App\Estado::where('id', $sending->estado_id)->get()->first();

        $this->say(
            'Código: ' . $sending->codigo .
            ', Cliente: ' . $user->nombre .
            ', Estado: ' . $sendingState->nombre .
            ', Fecha recogida: ' . $sending->fecharecogida .
            ', País recogida: ' . $sending->paisrecogida .
            ', Estado recogida: ' . $sending->estadorecogida .
            ', Dirección recogida: ' . $sending->direccionrecogida .
            ', País llegada: ' . $sending->paisllegada .
            ', Estado llegada: ' . $sending->estadollegada .
            ', Dirección llegada: ' . $sending->direccionllegada
        );

        $this->bot->say(
            'El paquete ' . $sending->codigo . ' ha sido modificado al estado ' . $sendingState->nombre,
            $user->code
        );

        $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
    }

    public function errorShowAllStates()
    {
        $message = Question::create('Error, ¿Desea volver a ver la lista de estados?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::OK) {
                    $this->showAllStates();
                } else {
                    $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function isIntoRange($number, $min, $max)
    {
        $formattedNumber = intval($number);
        return ($formattedNumber > 0 && $formattedNumber >= $min && $formattedNumber <= $max);
    }
}
