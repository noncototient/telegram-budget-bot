<?php

namespace App\Conversations;

use Log;
use App\User;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Conversations\Conversation;

class GetStarted extends Conversation
{
    protected $bot;

    protected $name;

    protected $password;

    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    public function askName()
    {
        return $this->ask("Welcome to Budget Tracker Bot! 🙌\n\nLet's start with your name?", function (Answer $answer) {
            $this->name = $answer->getText();

            $user = $this->createUser();

            $this->say("Awesome, " . $user->name  . "!\n\nHere is how you can start adding your expenses\n\n/add `$100 Groceries` - add an expense\n/cancel - cancel previously added expense", [
                'parse_mode' => 'Markdown'
            ]);
        });
    }

    public function createUser()
    {
        $user = $this->bot->getUser();

        $telegramId = $user->getId();

        Log::info('Telegram ID ' . $telegramId);

        $this->password = str_random(12);

        $user = User::firstOrCreate([
            'telegram_id' => $telegramId,
        ], [
            'telegram_id' => $telegramId,
            'name' => $this->name,
        ]);

        return $user;
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            return true;
        }

        return false;
    }

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askName();
    }
}
