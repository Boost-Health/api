<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FreshDeskNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Consultation $consultation) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $n = "\n\n";

        return (new MailMessage)
            ->subject(sprintf('%s Prescription from %s', $this->consultation->user->name, $this->consultation->doctor->name))
            ->cc('vadeshayo@gmail.com')
            ->line(sprintf('Consultation ID: %d', $this->consultation->id))
            ->line(sprintf('User Email: %s', $this->consultation->user->email))
            ->line(sprintf('User Phone Number: %s', $this->consultation->user->phone))
            ->line($n)
            ->line('Please see prescription below:')
            ->line($this->consultation->prescription)
            ->line($n)
            ->line('Please action immediately and update the consultation on the Admin')
            ->line($n);
    }
}
