<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Notifications\BillPaymentDue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a payment reminder for unpaid bills.
 * Dispatched by the scheduler daily for bills unpaid > 7 days.
 */
class SendBillPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 600];

    public function __construct(public readonly Bill $bill) {}

    public function handle(): void
    {
        // Skip if already paid
        if ($this->bill->payed) {
            return;
        }

        $patient = $this->bill->patient;

        if (! $patient?->email) {
            return;
        }

        $patient->notify(new BillPaymentDue($this->bill));

        Log::info('Bill payment reminder sent', [
            'bill_id'    => $this->bill->id,
            'patient_id' => $patient->id,
            'amount'     => $this->bill->amount,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Bill payment reminder failed', [
            'bill_id' => $this->bill->id,
            'error'   => $e->getMessage(),
        ]);
    }
}
