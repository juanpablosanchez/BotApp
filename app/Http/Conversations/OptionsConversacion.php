<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class OptionsConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->showOptions();
    }

    public function showOptions()
    {
        if ($this->isAdmin()) {
            $options = [
                Button::create('Consultar')->value(Constant::SENDINGS_CONSULT),
                Button::create('Administrar tipo de paquete')->value(Constant::PACK_TYPE_ADMIN),
                Button::create('Modificar estado de envío')->value(Constant::CHANGE_STATES),
            ];
        } else {
            $options = [
                Button::create('Iniciar sesión')->value(Constant::LOGIN),
                Button::create('Realizar envío')->value(Constant::SENDINGS_REGISTER),
                Button::create('Consultar')->value(Constant::SENDINGS_CONSULT),
            ];
        }

        $optionsMessage = Question::create('¿Qué acción deseas realizar?')
            ->addButtons($options);

        $this->ask($optionsMessage, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $option = $answer->getValue();
                $this->choosedOption($option);
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function choosedOption($option)
    {
        switch ($option) {
            case Constant::SENDINGS_CONSULT:
                $this->sendingsConsult();
                break;
            case Constant::PACK_TYPE_ADMIN:
                $this->packTypeAdmin();
                break;
            case Constant::CHANGE_STATES:
                $this->changeState();
                break;
            case Constant::LOGIN:
                $this->login();
                break;
            case Constant::SENDINGS_REGISTER:
                $this->sendingsRegister();
                break;
        }
    }

    public function sendingsConsult()
    {
        $this->bot->startConversation(new \App\Http\Conversations\SendingsConsultConversacion);
    }

    public function packTypeAdmin()
    {
        $this->bot->startConversation(new \App\Http\Conversations\PackTypeAdminConversacion);
    }

    public function changeState()
    {
        $this->bot->startConversation(new \App\Http\Conversations\ChangeStateConversacion);
    }

    public function login()
    {
        $this->bot->startConversation(new \App\Http\Conversations\LoginConversacion);
    }

    public function sendingsRegister()
    {
        $this->bot->startConversation(new \App\Http\Conversations\SendingsRegisterConversacion);
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
