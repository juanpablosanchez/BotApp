<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class PackTypeAdminConversacion extends Conversation
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
        $options = Question::create('Escoge una opción')
            ->addButtons([
                Button::create('Agregar')->value(Constant::ADD),
                Button::create('Editar')->value(Constant::EDIT),
                Button::create('Eliminar')->value(Constant::DELETE),
            ]);

        $this->ask($options, function (Answer $answer) {
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
            case Constant::ADD:
                $this->add();
                break;
            case Constant::EDIT:
                $this->edit();
                break;
            case Constant::DELETE:
                $this->remove();
                break;
        }
    }

    public function add()
    {
        $this->bot->startConversation(new \App\Http\Conversations\PackTypeAdminAddConversacion);
    }

    public function edit()
    {
        $this->bot->startConversation(new \App\Http\Conversations\PackTypeAdminEditConversacion);
    }

    public function remove()
    {
        $this->bot->startConversation(new \App\Http\Conversations\PackTypeAdminRemoveConversacion);
    }

}
