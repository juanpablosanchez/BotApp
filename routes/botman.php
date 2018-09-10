<?php

$botman = resolve('botman');

/*
$botman->hears('Hi', function ($bot) {
$bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
 */

/* INICIALIZATION */

$botman->hears('/start', function ($bot) {
    $bot->startConversation(new App\Http\Conversations\WelcomeConversacion);
});

$botman->hears('opciones|ayuda|comandos', function ($bot) {
    $bot->startConversation(new App\Http\Conversations\OptionsConversacion);
});

/* FAIL */
$botman->fallback(function ($bot) {
    $bot->reply("No entendí tu último mensaje.");
    $bot->startConversation(new App\Http\Conversations\OptionsConversacion);
});
