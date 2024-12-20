<?php

namespace App\Notifications;

use App\Enums\PromptCode;
use App\FlowCharts\PersonalHealthFlowChart;
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
        return (new MailMessage)
            ->subject(sprintf('%s needs your attention', $this->sender->name))
            ->cc('vadeshayo@gmail.com')
            ->when(config('app.env') !== 'local', fn ($mail) => $mail->cc('asiwajuakinadegoke@gmail.com'))
            ->when(config('app.env') !== 'local', fn ($mail) => $mail->cc('yvonne.elaigwu@gmail.com'))
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

        return $response->text;
    }
}
