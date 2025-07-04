<?php

namespace App\Notifications;

use App\Enums\ConsultationStatus;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Conversation;
use Throwable;

class NotifyDoctorNotification extends Notification
{
    use Queueable;

    public const NOTIFY_OTHER_ADMINS = false;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Conversation $conversation, public readonly User $sender) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        try {
            Consultation::updateOrCreate(['user_id' => $this->sender->id, 'status' => ConsultationStatus::PENDING], [
                'user_id' => $this->sender->id,
                'doctor_id' => $notifiable->id,
                'status' => ConsultationStatus::PENDING,
            ]);

            $this->sender->refresh();

            return (new MailMessage)
                ->subject(sprintf('%s needs your attention', $this->sender->name))
                ->cc('vadeshayo@gmail.com')
                ->when(NotifyAdminsOfUnavailableDoctorsNotification::shouldCopyOthers($this->sender), fn ($mail) => $mail->cc('asiwajuakinadegoke@gmail.com'))
                ->when(NotifyAdminsOfUnavailableDoctorsNotification::shouldCopyOthers($this->sender), fn ($mail) => $mail->cc('yvonne.elaigwu@gmail.com'))
                ->line(sprintf("Please see summary of %s's request below:", $this->sender->name))
                ->line($this->sender->context)
                ->line(sprintf('To contact %s, Please call %s', $this->sender->name, $this->sender->phone ?? 'N/A'))
                ->line('Thank you!');

        } catch (Throwable $th) {
            Log::error('notifications:notify-doctor', ['error' => $th->getMessage()]);
        }

        return new MailMessage;
    }
}
