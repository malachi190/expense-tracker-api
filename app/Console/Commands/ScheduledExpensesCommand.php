<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\ScheduledExpenses;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledExpensesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:scheduled-expenses-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled expenses for a user by specified date.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get scheduled expenses where current date matches date input
        $scheduled_expenses = ScheduledExpenses::whereDate("date", Carbon::now()->toDateString())->get();

        // loop through scheduled expenses and append to an Expense model instance
        foreach ($scheduled_expenses as $expense) {
            Expense::create([
                "item" => $expense->item,
                "price" => $expense->price,
                "quantity" => $expense->quantity,
                "decription" => $expense->description,
                "date" => $expense->date ?: Carbon::now(),
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
        }

        $this->info("Scheduled expense processed successfully!");
    }
}
