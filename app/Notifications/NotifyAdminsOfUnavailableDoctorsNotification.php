<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyAdminsOfUnavailableDoctorsNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly User $user) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(sprintf('[URGENT] %s needs a doctor\'s attention', $this->user->name))
            ->cc('vadeshayo@gmail.com')
            ->when(config('app.env') !== 'local', fn ($mail) => $mail->cc('asiwajuakinadegoke@gmail.com'))
            ->when(config('app.env') !== 'local', fn ($mail) => $mail->cc('yvonne.elaigwu@gmail.com'))
            ->line('There are currently no doctors available at this time.')
            ->line(sprintf('To contact %s, Please call %s', $this->user->name, $this->user->phone ?? 'N/A'))
            ->line('Thank you!');
    }
}
