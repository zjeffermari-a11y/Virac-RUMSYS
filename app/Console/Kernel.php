<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GenerateMonthlyBills::class,
        \App\Console\Commands\GenerateNewVendorBills::class,
        \App\Console\Commands\CleanupBillingData::class,
        \App\Console\Commands\ResetCurrentMonthReadings::class,
        \App\Console\Commands\ClearCurrentMonthReadings::class,
        \App\Console\Commands\SendBillingStatements::class, 
        \App\Console\Commands\SendPaymentReminders::class,  
        \App\Console\Commands\SendOverdueAlerts::class,   
        \App\Console\Commands\DeletePayment::class,
        \App\Console\Commands\CleanupSpecificMonthBilling::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('billing:generate')->monthlyOn(1, '00:00');
        $schedule->command('sms:send-payment-reminders')->dailyAt('08:00');
        $schedule->command('sms:send-overdue-alerts')->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}