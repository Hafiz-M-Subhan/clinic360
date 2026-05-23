<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends an appointment reminder notification to the patient.
 * Dispatched by the scheduler 24 hours before each appointment.
 *
 * Queue: notifications
 * Retries: 3 with backoff [30s, 120s, 300s]
 */
class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public readonly Appointment $appointment) {}

    public function handle(): void
    {
        $patient = $this->appointment->patient;

        if (! $patient?->email) {
            return;
        }

        $patient->notify(new AppointmentReminder($this->appointment));

        Log::info('Appointment reminder sent', [
            'appointment_id' => $this->appointment->id,
            'patient_id'     => $patient->id,
            'scheduled_at'   => $this->appointment->intime,
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Appointment reminder failed', [
            'appointment_id' => $this->appointment->id,
            'error'          => $e->getMessage(),
        ]);
    }
}
