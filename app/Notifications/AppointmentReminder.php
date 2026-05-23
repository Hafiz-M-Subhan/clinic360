<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;
        $doctor      = $appointment->doctor;
        $scheduledAt = \Carbon\Carbon::parse($appointment->intime)->format('l, F j Y \a\t g:i A');

        return (new MailMessage)
            ->subject('Appointment Reminder — ' . $scheduledAt)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a reminder about your upcoming appointment.')
            ->line('**Doctor:** ' . ($doctor?->name ?? 'To be confirmed'))
            ->line('**Date & Time:** ' . $scheduledAt)
            ->action('View Appointment Details', url('/'))
            ->line('If you need to reschedule, please contact us as soon as possible.')
            ->salutation('Clinic360 Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'scheduled_at'   => $this->appointment->intime,
        ];
    }
}
