<?php

namespace App\Http\Conversations;

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
        $this->SENDINGS_CONSULT = 'consult';
        $this->PACK_TYPE_ADMIN = 'tipopaquete';
        $this->CHANGE_STATES = 'estados';
        $this->LOGIN = 'login';
        $this->SENDINGS_REGISTER = 'sender';

        $this->showOptions();
    }

    public function showOptions()
    {
        if ($this->isAdmin()) {
            $options = [
                Button::create('Consultar')->value($this->SENDINGS_CONSULT),
                Button::create('Administrar tipo de paquete')->value($this->PACK_TYPE_ADMIN),
                Button::create('Modificar estado de envío')->value($this->CHANGE_STATES),
            ];
        } else {
            $options = [
                Button::create('Iniciar sesión')->value($this->LOGIN),
                Button::create('Realizar envío')->value($this->SENDINGS_REGISTER),
                Button::create('Consultar')->value($this->SENDINGS_CONSULT),
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
            case $this->SENDINGS_CONSULT:
                $this->sendingsConsult();
                break;
            case $this->PACK_TYPE_ADMIN:
                $this->packTypeAdmin();
                break;
            case $this->CHANGE_STATES:
                $this->changeState();
                break;
            case $this->LOGIN:
                $this->login();
                break;
            case $this->SENDINGS_REGISTER:
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

    }
}
