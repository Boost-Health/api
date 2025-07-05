<?php

namespace App\Jobs;

use App\Clients\SlackBotClient;
use App\Enums\ConsultationStatus;
use App\Enums\PromptCode;
use App\Models\Consultation;
use App\Models\Prompt;
use App\Notifications\FreshDeskNotification;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class SlackAIMentionJob implements ShouldQueue
{
    use Queueable;

    private string $message;

    public function __construct(private readonly MessageObject $messageObject)
    {
        // <@U08TYENF57H> is the AI Bot
        $this->message = trim(Str::replace('<@U08TYENF57H>', '', $this->messageObject->message));
    }

    public function handle(): void
    {
        match (true) {
            Str::contains(strtolower($this->message), 'prescription') => $this->handlePrescription(),
            $this->wantsToEndConversation() => $this->endConversation(),
            $this->wantsSlackID() => $this->sendSlackID(),
            default => $this->reply('Sorry, I do not understand your message.'),
        };
    }

    private function wantsToEndConversation(): bool
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt(Prompt::for(PromptCode::AI_MENTION_CHECK_CONVERSATION_END, ['reply' => $this->message]))
            ->asText();

        $this->log('wants-to-end-conversation', ['response' => $response->text]);

        return Str::contains($response->text, 'TRUE');
    }

    private function wantsSlackID(): bool
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt(Prompt::for(PromptCode::AI_MENTION_ASK_FOR_SLACK_ID, ['input' => $this->message]))
            ->asText();

        $this->log('ask-for-slack-id', ['response' => $response->text]);

        return Str::contains($response->text, 'TRUE');
    }

    private function endConversation(): void
    {
        app(ConversationService::class)->endConversation($this->messageObject);
    }

    private function sendSlackID(): void
    {
        if ($slackUserId = Str::of($this->message)->match('/<@([A-Z0-9]+)>/')) {
            $this->reply(sprintf('The Slack ID of <@%s> is `%s`', $slackUserId, $slackUserId));
        } else {
            $this->reply('Could not find Slack ID in your message. Please make sure you mention the user and ask me something like: What is the Slack ID of @mention');
        }
    }

    private function handlePrescription(): void
    {
        $consultation = Consultation::query()
            ->whereUserId($this->messageObject->to->user->id)
            ->whereDoctorId($this->messageObject->from->user->id)
            ->whereStatus(ConsultationStatus::PENDING)
            ->orderByDesc('id')
            ->first();

        if (blank($consultation)) {
            $this->log('no:consultation');

            return;
        }

        $consultation->update(['prescription' => $this->message]);
        Notification::route('mail', 'support@boosthealth.freshdesk.com')->notify(new FreshDeskNotification($consultation));

        $this->reply('Prescription received. An agent has been notified and will action your request immediately. You can mention me and let me know this consultation is complete.');
    }

    private function reply(string $message): void
    {
        app(SlackBotClient::class)->aiMessage($this->messageObject->to->user, $message);
    }

    private function log(string $prefix, array $context = []): void
    {
        Log::info("slack-ai-mention:{$prefix}", ['message' => $this->message] + $context);
    }
}
