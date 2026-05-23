<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillPaymentDue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Bill $bill) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount  = number_format($this->bill->amount ?? 0, 2);
        $dueDate = \Carbon\Carbon::parse($this->bill->created_at)->addDays(7)->format('F j, Y');

        return (new MailMessage)
            ->subject('Payment Reminder — Bill #' . $this->bill->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have an outstanding balance that requires your attention.')
            ->line('**Bill #:** ' . $this->bill->id)
            ->line('**Amount Due:** $' . $amount)
            ->line('**Due Date:** ' . $dueDate)
            ->action('Pay Now', url('/'))
            ->line('If you have already made payment, please disregard this notice.')
            ->salutation('Clinic360 Billing Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bill_id' => $this->bill->id,
            'amount'  => $this->bill->amount,
        ];
    }
}
