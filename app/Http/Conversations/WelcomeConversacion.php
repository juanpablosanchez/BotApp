<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use Exception;

class WelcomeConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->greet();
    }

    public function greet()
    {
        $this->user = $this->getUser();
        $firstname = $this->user->nombre;

        $this->resetSession($this->user->id);

        if ($firstname == '') {
            $this->say('Bienvenid@ a nuestro sistema de mensajería.');
            $this->askName();
        } else {
            $this->say("Hola $firstname, bienvenid@ a nuestra empresa de mensajería.");
            $this->saveUser();
            $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
        }

    }

    public function askName()
    {
        $this->ask("¿Cuál es tu nombre?", function (Answer $answer) {
            $name = $answer->getText();
            $this->user->nombre = $name;
            $this->saveUser();

            $this->say("Hola $name.");
            $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
        });
    }

    public function getUser()
    {
        $channel = $this->bot->getUser();
        $id = $channel->getId();

        $userSaved = \App\Cliente::where('codigo', $id)->get();
        //\Log::info($user->isEmpty());

        if ($userSaved->isEmpty()) {
            $username = $channel->getUsername() ?: '';
            $firstname = $channel->getFirstName() ?: $username;
            $lastname = $channel->getLastName() ?: '';

            $user = (object) array(
                'id' => '',
                'codigo' => $id,
                'nombre' => $firstname,
                'apellido' => $lastname,
            );
        } else {
            $currentUser = $userSaved->first();

            $user = (object) array(
                'id' => $currentUser->id,
                'codigo' => $currentUser->codigo,
                'nombre' => $currentUser->nombre,
                'apellido' => $currentUser->apellido,
            );
        }

        return $user;
    }

    public function saveUser()
    {
        // Crear o actualizar la información del usuario en sesión
        $cliente = \App\Cliente::firstOrNew(array(
            'codigo' => $this->user->codigo,
            'nombre' => $this->user->nombre,
            'apellido' => $this->user->apellido,
        ));

        $cliente->save();
    }

    public function resetSession($userId)
    {
        if (empty($userId)) {
            return;
        }

        try {
            \App\Administrador::where('cliente_id', $userId)->delete();
        } catch (Exception $e) {}

        return;
    }
}
