<?php

namespace App\Http\Conversations;

use App\Http\Helper\Helper;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class LoginConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askUsername();
    }

    public function stopsConversation(IncomingMessage $message)
    {
        return Helper::stopConversation($message->getText());
    }

    public function askUsername()
    {
        $this->ask("Ingrese su usuario", function (Answer $answer) {
            $this->username = $answer->getText();
            $this->askPassword();
        });
    }

    public function askPassword()
    {
        $this->ask("Ingrese su contraseña", function (Answer $answer) {
            $this->password = $answer->getText();
            $logged = $this->login();

            if ($logged) {
                $this->say('Ha iniciado sesión correctamente');
            } else {
                $this->say('Credenciales inválidas.');
            }
            $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
        });
    }

    public function login()
    {
        if ($this->username == env('ADMIN_USERNAME') && $this->password == env('ADMIN_PASSWORD')) {
            $channel = $this->bot->getUser();
            $id = $channel->getId();

            $userSaved = \App\Cliente::where('codigo', $id)->get();

            if ($userSaved->isEmpty()) {
                return false;
            }

            $userAdmin = \App\Administrador::create(array(
                'cliente_id' => $userSaved->first()->id,
            ));

            return true;
        }
    }
}
