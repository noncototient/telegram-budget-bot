<?php

namespace App\Http\Controllers\Botman;

use App\User;
use Carbon\Carbon;
use BotMan\BotMan\BotMan;
use App\Conversations\AddExpense;
use App\Http\Controllers\Controller;

class UserExpensesController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    public function index(BotMan $bot)
    {
        $user = $bot->getUser();
        $user = User::where('telegram_id', $user->getId())->first();

        $expensesToday = $user->expenses()->where([
            ['created_at', '>=', Carbon::today()->startOfDay()],
            ['created_at', '<=', Carbon::today()->endOfDay()],
        ])
        ->get();

        $totalExpensesToday = $expensesToday->sum('amount');

        $expensesToday = $expensesToday->map(function ($expense) {
            return '$' . $expense->amount . ' - ' . $expense->description . "\n";
        })->toArray();

        $expensesToday = implode($expensesToday);

        $bot->reply("Here is your expenses breakdown:\n\n*Today*\n$expensesToday---\n*$" . $totalExpensesToday . ' - Total*', [
            'parse_mode' => 'Markdown'
        ]);
    }

    /**
     * Store user's expense
     * @param  BotMan $bot, Number $amount, String $description
     */
    public function store(BotMan $bot, $amount, $description)
    {
        $user = $bot->getUser();
        $user = User::where('telegram_id', $user->getId())->first();

        $amount = (float) str_replace('$', '', $amount);
        $user->expenses()->create([
            'amount' => $amount,
            'description' => $description,
        ]);

        $bot->reply('Great! I will save $' . $amount . ' as ' . $description . '!');
    }

    public function destroy(BotMan $bot)
    {
        $user = $bot->getUser();
        $user = User::where('telegram_id', $user->getId())->first();

        $lastExpense = $user->expenses()->latest()->first();

        $deleted = $lastExpense;

        $lastExpense->delete();

        $bot->reply('Right! Deleted ' . $deleted->description . ', which was $' . $deleted->amount . '!');
    }

    public function destroyByDescription(BotMan $bot, $description)
    {
        $user = $bot->getUser();
        $user = User::where('telegram_id', $user->getId())->first();

        $expense = $user->expenses()->where('description', 'LIKE', '%' . $description . '%')->first();

        if (empty($expense)) {
            $bot->reply('Couldn\'t find expense with that description.');
            return;
        }

        $deleted = $expense;

        $expense->delete();

        $bot->reply('Right! Deleted ' . $deleted->description . ', which was $' . $deleted->amount . '!');
    }

    /**
     * Store user's expense as a conversation
     * @param  BotMan $bot
     */
    public function ask(BotMan $bot)
    {
        $bot->startConversation(new AddExpense($bot));
    }
}
