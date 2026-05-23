<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Generates and caches daily analytics report.
 * Runs at midnight via the scheduler.
 * Cached for 24 hours so the dashboard loads instantly.
 */
class GenerateDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $report = [
            'date'                  => today()->toDateString(),
            'new_patients_today'    => Patient::whereDate('created_at', today())->count(),
            'appointments_today'    => Appointment::whereDate('created_at', today())->count(),
            'revenue_today'         => Bill::whereDate('created_at', today())->where('payed', true)->sum('amount'),
            'pending_bills'         => Bill::where('payed', false)->count(),
            'pending_bills_amount'  => Bill::where('payed', false)->sum('amount'),
            'monthly_revenue'       => Bill::whereMonth('created_at', now()->month)
                                          ->where('payed', true)
                                          ->sum('amount'),
            'monthly_patients'      => Patient::whereMonth('created_at', now()->month)->count(),
        ];

        // Cache for 24 hours — dashboard reads from cache, never hits DB on every load
        Cache::put('daily_report', $report, now()->addHours(24));

        Log::info('Daily report generated', ['date' => $report['date']]);
    }
}
