<?php

namespace App\Http\Conversations;

use App\Http\Helper\Helper;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class SendingsConsultConversacion extends Conversation
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
        if ($this->isAdmin()) {
            $this->sendings = \App\Envio::all();
        } else {
            $user = $this->getUser();
            if ($user == null) {
                $this->say('No estas registrado, para registrarse use el siguiente comando "/start"');
                return;
            }
            $this->sendings = \App\Envio::where('cliente_id', $user->id)->get();
        }

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
            }
        });
    }

    public function getUser()
    {
        $channel = $this->bot->getUser();
        $id = $channel->getId();

        $userSaved = \App\Cliente::where('codigo', $id)->get();

        if ($userSaved->isEmpty()) {
            return null;
        } else {
            $currentUser = $userSaved->first();

            return (object) array(
                'id' => $currentUser->id,
                'codigo' => $currentUser->codigo,
                'cedula' => $currentUser->cedula,
                'nombre' => $currentUser->nombre,
                'apellido' => $currentUser->apellido,
                'telefono' => $currentUser->telefono,
            );
        }
    }

    public function isAdmin()
    {
        $channel = $this->bot->getUser();
        $id = $channel->getId();

        $userSaved = \App\Cliente::where('codigo', $id)->get();

        if ($userSaved->isEmpty()) {
            return false;
        }

        $userAdmin = \App\Administrador::where('cliente_id', $userSaved->first()->id)->count();

        return $userAdmin > 0;
    }
}
