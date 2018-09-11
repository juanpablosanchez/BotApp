<?php

namespace App\Http\Conversations;

use App\Http\Helper\Constant;
use App\Http\Helper\Helper;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
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
        $this->sendingInfo = (object) array();
        $this->packages = array();
        $this->packsTypeList = \App\TipoPaquete::orderBy('nombre', 'asc')->get();

        $this->getUserInfo();
    }

    public function stopsConversation(IncomingMessage $message)
    {
        return Helper::stopConversation($message->getText());
    }

    public function getUserInfo()
    {
        $this->user = $this->getUser();

        if ($this->user == null) {
            $this->say('No estas registrado, para registrarse use el siguiente comando "/start"');
        } else {
            if (empty($this->user->cedula) || empty($this->user->nombre) || empty($this->user->apellido) || empty($this->user->telefono)) {
                $this->askDocument();
            } else {
                $this->say('cédula: ' . $this->user->cedula . ', nombre: ' . $this->user->nombre . ', apellido: ' . $this->user->apellido . ', teléfono: ' . $this->user->telefono);
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

    public function askDocument()
    {
        $this->ask("Ingrese su documento de identidad", function (Answer $answer) {
            $this->user->cedula = trim($answer->getText());
            if (empty($this->user->cedula)) {
                $this->say('Documento no válido');
                $this->repeat();
            } else {
                $this->askFirstname();
            }
        });
    }

    public function askFirstname()
    {
        $this->ask("Ingrese su nombre", function (Answer $answer) {
            $this->user->nombre = trim($answer->getText());
            if (empty($this->user->nombre)) {
                $this->say('Nombre no válido');
                $this->repeat();
            } else {
                $this->askLastname();
            }
        });
    }

    public function askLastname()
    {
        $this->ask("Ingrese su apellido", function (Answer $answer) {
            $this->user->apellido = trim($answer->getText());
            if (empty($this->user->apellido)) {
                $this->say('Apellido no válido');
                $this->repeat();
            } else {
                $this->askPhone();
            }
        });
    }

    public function askPhone()
    {
        $this->ask("Ingrese su teléfono", function (Answer $answer) {
            $this->user->telefono = trim($answer->getText());
            if (empty($this->user->telefono)) {
                $this->say('Teléfono no válido');
                $this->repeat();
            } else {
                $this->saveUserInfo();
            }
        });
    }

    public function saveUserInfo()
    {
        \App\Cliente::where('id', $this->user->id)
            ->update([
                'cedula' => $this->user->cedula,
                'nombre' => $this->user->nombre,
                'apellido' => $this->user->apellido,
                'telefono' => $this->user->telefono,
            ]);

        $this->requestPackageInfo();
    }

    public function requestPackageInfo()
    {
        $messageButton = array();
        foreach ($this->packsTypeList as $packType) {
            array_push($messageButton, Button::create($packType->nombre)->value($packType->id));
        }
        $message = Question::create('Selecciona un tipo de paquete')->addButtons($messageButton);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $this->newPackType = $answer->getValue();
                $this->askWeight();
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function askWeight()
    {
        $this->ask("Ingrese el peso del paquete", function (Answer $answer) {
            $this->newWeight = trim($answer->getText());
            if (intval($this->newWeight) > 0) {
                $this->questionForAddNewPackage();
            } else {
                $this->say('Peso no válido');
                $this->repeat();
            }
        });
    }

    public function questionForAddNewPackage()
    {
        array_push($this->packages, [
            'tipopaquete_id' => $this->newPackType,
            'peso' => $this->newWeight,
        ]);

        $message = Question::create('¿Desea registrar otro paquete?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::OK) {
                    $this->requestPackageInfo();
                } else {
                    $this->confirmPackageList(0);
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->requestPackTypeToDelete();
            }
        });
    }

    public function confirmPackageList($index)
    {
        if ($index >= count($this->packages)) {
            $this->requestSendingInfo();
        } else {
            $this->packageIndex = $index;
            $package = (object) $this->packages[$index];
            $packTypeSelected = $this->packsTypeList->where('id', $package->tipopaquete_id)->first();
            $message = Question::create('¿Desea confirmar este paquete?. Tipo paquete: ' . $packTypeSelected->nombre . ', Peso: ' . $package->peso)
                ->addButtons([
                    Button::create('Si')->value(Constant::OK),
                    Button::create('No')->value(Constant::KO),
                ]);

            $this->ask($message, function (Answer $answer) {
                if ($answer->isInteractiveMessageReply()) {
                    if ($answer->getValue() == Constant::OK) {
                        $this->confirmPackageList($this->packageIndex + 1);
                    } else {
                        $this->confirmRemovePackageFromList();
                    }
                } else {
                    $this->say('Por favor elige una opción de la lista.');
                    $this->confirmPackageList($this->packageIndex);
                }
            });
        }
    }

    public function confirmRemovePackageFromList()
    {
        $message = Question::create('¿Esta seguro que desea eliminarlo de la lista?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::OK) {
                    $this->removePackageFromList($this->packageIndex);
                } else {
                    $this->confirmPackageList($this->packageIndex + 1);
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function removePackageFromList($index)
    {
        array_splice($this->packages, $index, 1);
        $this->confirmPackageList($this->packageIndex);
    }

    public function requestSendingInfo()
    {
        $message = Question::create('¿Desea confirmar el envío?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::OK) {
                    $this->askDepartureDate();
                } else {
                    $this->confirmCancelSending();
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->confirmPackageList($this->packageIndex);
            }
        });
    }

    public function confirmCancelSending()
    {
        $message = Question::create('¿Esta seguro que desea cancelar el envío?')
            ->addButtons([
                Button::create('Si')->value(Constant::OK),
                Button::create('No')->value(Constant::KO),
            ]);

        $this->ask($message, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() == Constant::KO) {
                    $this->askDepartureDate();
                } else {
                    $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->repeat();
            }
        });
    }

    public function askDepartureDate()
    {
        $this->ask("Ingrese la fecha de recogida del paquete (dd/mm/yyyy)", function (Answer $answer) {
            $this->sendingInfo->fecharecogida = trim($answer->getText());
            if ($this->validateDate($this->sendingInfo->fecharecogida)) {
                $this->askCountryDeparture();
            } else {
                $this->say('Fecha inválida');
                $this->repeat();
            }
        });
    }

    public function askCountryDeparture()
    {
        $this->ask("Ingrese el país de recogida del paquete", function (Answer $answer) {
            $this->sendingInfo->paisrecogida = trim($answer->getText());
            if (!empty($this->sendingInfo->paisrecogida)) {
                $this->askStateDeparture();
            } else {
                $this->say('País inválida');
                $this->repeat();
            }
        });
    }

    public function askStateDeparture()
    {
        $this->ask("Ingrese el estado de recogida del paquete", function (Answer $answer) {
            $this->sendingInfo->estadorecogida = trim($answer->getText());
            if (!empty($this->sendingInfo->estadorecogida)) {
                $this->askAddressDeparture();
            } else {
                $this->say('Estado inválida');
                $this->repeat();
            }
        });
    }

    public function askAddressDeparture()
    {
        $this->ask("Ingrese la dirección de recogida del paquete", function (Answer $answer) {
            $this->sendingInfo->direccionrecogida = trim($answer->getText());
            if (!empty($this->sendingInfo->direccionrecogida)) {
                $this->askCountryArrival();
            } else {
                $this->say('Dirección inválida');
                $this->repeat();
            }
        });
    }

    public function askCountryArrival()
    {
        $this->ask("Ingrese el país de llegada del paquete", function (Answer $answer) {
            $this->sendingInfo->paisllegada = trim($answer->getText());
            if (!empty($this->sendingInfo->paisllegada)) {
                $this->askStateArrival();
            } else {
                $this->say('País inválido');
                $this->repeat();
            }
        });
    }

    public function askStateArrival()
    {
        $this->ask("Ingrese el estado de llegada del paquete", function (Answer $answer) {
            $this->sendingInfo->estadollegada = trim($answer->getText());
            if (!empty($this->sendingInfo->estadollegada)) {
                $this->askAddressArrival();
            } else {
                $this->say('Estado inválido');
                $this->repeat();
            }
        });
    }

    public function askAddressArrival()
    {
        $this->ask("Ingrese la dirección de llegada del paquete", function (Answer $answer) {
            $this->sendingInfo->direccionllegada = trim($answer->getText());
            if (!empty($this->sendingInfo->direccionllegada)) {
                $this->registerSending();
            } else {
                $this->say('Estado inválida');
                $this->repeat();
            }
        });
    }

    public function registerSending()
    {
        $sendingState = \App\Estado::where('nombre', Constant::DEFAULT_SENDING_STATE)->get()->first();

        $sending = \App\Envio::create([
            'cliente_id' => $this->user->id,
            'estado_id' => $sendingState->id,
            'codigo' => '',
            'fecharecogida' => $this->sendingInfo->fecharecogida,
            'paisrecogida' => $this->sendingInfo->paisrecogida,
            'estadorecogida' => $this->sendingInfo->estadorecogida,
            'direccionrecogida' => $this->sendingInfo->direccionrecogida,
            'paisllegada' => $this->sendingInfo->paisllegada,
            'estadollegada' => $this->sendingInfo->estadollegada,
            'direccionllegada' => $this->sendingInfo->direccionllegada,
        ]);
        $this->sendingInfo->codigo = $this->user->codigo . $sending->id;
        $sending->codigo = $this->sendingInfo->codigo;
        $sending->save();

        foreach ($this->packages as $package) {
            \App\Paquete::create([
                'envio_id' => $sending->id,
                'tipopaquete_id' => $package['tipopaquete_id'],
                'peso' => $package['peso'],
            ]);
        }
        $this->sendMessageToAdmin();

        $this->say('El envío ha sido registrado');
        $this->bot->startConversation(new \App\Http\Conversations\OptionsConversacion);
    }

    public function sendMessageToAdmin()
    {
        $admins = $this->getAdminList();

        foreach ($admins as $admin) {
            $this->bot->say(
                'Se ha registrado un envío desde su empresa de mensajería. Código de envío: ' . $this->sendingInfo->codigo,
                $admin,
                TelegramDriver::class
            );
            foreach ($this->packages as $package) {
                $packType = $this->packsTypeList->where('id', $package['tipopaquete_id'])->first();
                $this->bot->say(
                    'Paquete: Tipo de paquete ' . $packType->nombre . ', peso: ' . $package['peso'],
                    $admin,
                    TelegramDriver::class
                );
            }
            $this->bot->say(
                'Información del cliente: Nombre: ' . $this->user->nombre . ', apellido: ' . $this->user->apellido . ', teléfono; ' . $this->user->telefono,
                $admin,
                TelegramDriver::class
            );
        }
    }

    public function getAdminList()
    {
        $adminsChannelsID = array();
        $admins = \App\Administrador::all();

        foreach ($admins as $admin) {
            $user = \App\Cliente::where('id', $admin->cliente_id)->get()->first();
            array_push($adminsChannelsID, $user->codigo);
        }

        return $adminsChannelsID;
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

    private function validateDate($dateString)
    {
        // Procesar la fecha recibida para obtener sus partes
        $splits = explode("/", $dateString);

        // Verificar que tenga tres partes (d/m/a)
        if (count($splits) != 3) {
            return false;
        }

        // Verifcar que sea una fecha válida
        $control = checkdate($splits[1], $splits[0], $splits[2]);

        if (!$control) {
            return false;
        }

        $now = time();
        $date = mktime(23, 59, 59, intval($splits[1]), intval($splits[0]), intval($splits[2]));

        // Verificar que sea una fecha futura
        return ($date > $now);
    }
}
