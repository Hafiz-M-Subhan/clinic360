<?php

namespace App\Console;

use App\Jobs\GenerateDailyReport;
use App\Jobs\SendAppointmentReminder;
use App\Jobs\SendBillPaymentReminder;
use App\Models\Appointment;
use App\Models\Bill;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        // Regenerate the cached daily analytics report at midnight every day
        $schedule->job(new GenerateDailyReport)->dailyAt('00:00')
            ->name('generate-daily-report')
            ->withoutOverlapping();

        // Send appointment reminders for appointments starting within the next 24 hours
        $schedule->call(function () {
            Appointment::whereBetween('intime', [now()->addHours(23), now()->addHours(25)])
                ->with('patient')
                ->get()
                ->each(fn ($appointment) => SendAppointmentReminder::dispatch($appointment));
        })->dailyAt('08:00')->name('send-appointment-reminders')->withoutOverlapping();

        // Send payment reminders for bills unpaid for more than 7 days
        $schedule->call(function () {
            Bill::where('payed', false)
                ->where('created_at', '<=', now()->subDays(7))
                ->with('patient')
                ->get()
                ->each(fn ($bill) => SendBillPaymentReminder::dispatch($bill));
        })->dailyAt('09:00')->name('send-bill-reminders')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
