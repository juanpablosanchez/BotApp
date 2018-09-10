<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class SendingsRegisterConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->packages = array();

        $this->getUserInfo();
    }

    public function getUserInfo()
    {
        $this->user = $this->getUser();

        if ($this->user == null) {
            $this->say('No estas registrado, para registrarse use el siguiente comando "/start"');
        } else {
            if (empty($this->cedula) || empty($this->nombre) || empty($this->apellido) || empty($this->telefono)) {
                $this->requestUserInfo();
            } else {
                $message = Question::create('¿Desea modificar esta información?')
                    ->addButtons([
                        Button::create('Si')->value(Constant::OK),
                        Button::create('No')->value(Constant::KO),
                    ]);

                $this->ask($message, function (Answer $answer) {
                    if ($answer->isInteractiveMessageReply()) {
                        if ($answer->getValue() == Constant::OK) {
                            $this->askDocument();
                        } else {
                            $this->requestPackageInfo();
                        }
                    } else {
                        $this->say('Por favor elige una opción de la lista.');
                        $this->repeat();
                    }
                });
            }
        }
    }

    public function askDocument(){
        $this->ask("Ingrese su documento de identidad", function (Answer $answer) {
            $this->user->cedula = trim($answer->getText());
            if(empty($this->user->cedula)){
                $this->say('Documento no válido');
                $this->repeat();
            } else {
                $this->askFirstname();
            }
        });
    }

    public function askFirstname(){
        $this->ask("Ingrese su nombre", function (Answer $answer) {
            $this->user->nombre = trim($answer->getText());
            if(empty($this->user->nombre)){
                $this->say('Nombre no válido');
                $this->repeat();
            } else {
                $this->askLastname();
            }
        });
    }

    public function askLastname(){
        $this->ask("Ingrese su apellido", function (Answer $answer) {
            $this->user->apellido = trim($answer->getText());
            if(empty($this->user->apellido)){
                $this->say('Apellido no válido');
                $this->repeat();
            } else {
                $this->askPhone();
            }
        });
    }

    public function askPhone(){
        $this->ask("Ingrese su teléfono", function (Answer $answer) {
            $this->user->telefono = trim($answer->getText());
            if(empty($this->user->telefono)){
                $this->say('Teléfono no válido');
                $this->repeat();
            } else {
                $this->requestPackageInfo();
            }
        });
    }

    public function requestPackageInfo()
    {
        // TODO
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
                'codigo' => $currentUser->codigo,
                'cedula' => $currentUser->cedula,
                'nombre' => $currentUser->nombre,
                'apellido' => $currentUser->apellido,
                'telefono' => $currentUser->telefono,
            );
        }
    }
}
