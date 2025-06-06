<?php

namespace App\Notifications;

use App\Enums\ConsultationStatus;
use App\Enums\PromptCode;
use App\FlowCharts\PersonalHealthFlowChart;
use App\Models\Consultation;
use App\Models\Prompt;
use App\Models\User;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Musonza\Chat\Models\Conversation;

class NotifyDoctorNotification extends Notification
{
    use Queueable;

    public Consultation $consultation;

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
        $this->consultation = Consultation::create([
            'user_id' => $this->sender->id,
            'doctor_id' => $notifiable->id,
            'status' => $notifiable->isDoctor() ? ConsultationStatus::COMPLETED : ConsultationStatus::PENDING,
        ]);

        return (new MailMessage)
            ->subject(sprintf('%s needs your attention', $this->sender->name))
            ->cc('vadeshayo@gmail.com')
            ->when(false && NotifyAdminsOfUnavailableDoctorsNotification::shouldCopyOthers($this->sender), fn ($mail) => $mail->cc('asiwajuakinadegoke@gmail.com'))
            ->when(false && NotifyAdminsOfUnavailableDoctorsNotification::shouldCopyOthers($this->sender), fn ($mail) => $mail->cc('yvonne.elaigwu@gmail.com'))
            ->line(sprintf("Please see summary of %s's request below:", $this->sender->name))
            ->line($this->getIssueSummary())
            ->line(sprintf('To contact %s, Please call %s', $this->sender->name, $this->sender->phone ?? 'N/A'))
            ->line('Thank you!');
    }

    public function getIssueSummary()
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withSystemPrompt(Prompt::for(PromptCode::SUMMARIZE_CONVERSATION_FOR_DOCTOR))
            ->withMessages(PersonalHealthFlowChart::getFormattedMessagesForPrism($this->conversation, 20))
            ->generate();

        $this->consultation->update(['complaint' => $response->text]);

        if (config('app.env') === 'local') {
            $this->consultation->update(['conversation' => PersonalHealthFlowChart::getFormattedMessagesForPrism($this->conversation, 20, true)]);
        }

        return $response->text;
    }
}
