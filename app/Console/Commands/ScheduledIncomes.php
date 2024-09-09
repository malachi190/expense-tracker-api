<?php

namespace App\Console\Commands;

use App\Models\Income;
use App\Models\ScheduledIncome;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incomes:scheduled-incomes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled incomes for a user by specified date.';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get scheduled incomes where the current date matches end date
        $schduled_incomes = ScheduledIncome::whereDate("end_date", Carbon::now()->toDateString())->get();

        foreach ($schduled_incomes as $income){
            Income::create([
                "income" => $income->amount,
                "description" => $income->description,
                "date" => Carbon::now(),
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
        }

        $this->info("Scheduled income processed successfully!");
    }
}
