<?php

use App\Conversations\GetStarted;

$botman = resolve('botman');

$botman->hears('/start', function ($bot) {
    $bot->startConversation(new GetStarted($bot));
});


$botman->hears('/add {amount} {description}', 'App\Http\Controllers\Botman\UserExpensesController@store');
$botman->hears('/add', 'App\Http\Controllers\Botman\UserExpensesController@ask');
$botman->hears('/stats', 'App\Http\Controllers\Botman\UserExpensesController@index');
$botman->hears('/delete', 'App\Http\Controllers\Botman\UserExpensesController@destroy');
$botman->hears('/delete {description}', 'App\Http\Controllers\Botman\UserExpensesController@destroyByDescription');
$botman->fallback(function ($bot) {
    $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ...');
});

// $botman->hears('/add {amount} {description}', function($bot, $amount, $description) {
//     $bot->reply("Great! I will save {$amount} as {$description}!");
// });

// $botman->hears('/add', function($bot) {
//     $bot->reply("Please provide both the amount and the description, like this /add $100 Candy. Try again by typing '/' and select '/add' from the menu");
// });

// $botman->hears('Start conversation', BotManController::class . '@startConversation');
