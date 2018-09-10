<?php

$botman = resolve('botman');

/*
$botman->hears('Hi', function ($bot) {
$bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
 */

/* INICIALIZTION */

$botman->hears('/start', function ($bot) {
    $bot->startConversation(new App\Http\Conversations\WelcomeConversacion);
});
