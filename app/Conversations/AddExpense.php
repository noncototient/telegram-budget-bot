<?php

namespace App\Conversations;

use App\User;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Conversations\Conversation;

class AddExpense extends Conversation
{
    protected $user;

    protected $amount;

    protected $description;

    public function __construct($bot)
    {
        $this->bot = $bot;
        $this->user = $this->bot->getUser();
    }

    public function askAmount()
    {
        $this->ask('Cool! How much would you like to add?', function (Answer $answer) {
            $this->amount = $answer->getText();

            $this->amount = (float) str_replace('$', '', $this->amount);

            $this->askDescription();
        });
    }

    public function askDescription()
    {
        $this->ask('What would the description be?', function (Answer $answer) {
            $this->description = $answer->getText();

            $this->say('Great! I will save $' . $this->amount . ' as ' . $this->description . '!');

            $this->storeExpense();
        });
    }

    public function storeExpense()
    {
        $user = User::where('telegram_id', $this->user->getId())->first();

        $user->expenses()->create([
            'amount' => $this->amount,
            'description' => $this->description,
        ]);

        return;
    }

    public function stopsConversation(IncomingMessage $message)
    {
        if ($message->getText() == 'stop') {
            $this->say('Okay, aborting');
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
        $this->askAmount();
    }
}
