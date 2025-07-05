<?php

namespace App\Jobs;

use App\Clients\SlackBotClient;
use App\Enums\PromptCode;
use App\Models\Prompt;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class SlackAIMentionJob implements ShouldQueue
{
    use Queueable;

    private string $message;

    public function __construct(private readonly MessageObject $messageObject)
    {
        // <@U08UWES6APJ> is the AI Bot
        $this->message = trim(Str::replace('<@U08UWES6APJ>', '', $this->messageObject->message));
    }

    public function handle(ConversationService $conversationService): void
    {
        match (true) {
            $this->wantsToEndConversation() => $conversationService->endConversation($this->messageObject),
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

    private function sendSlackID(): void
    {
        if ($slackUserId = Str::of($this->message)->match('/<@([A-Z0-9]+)>/')) {
            $this->reply(sprintf('The Slack ID of <@%s> is `%s`', $slackUserId, $slackUserId));
        } else {
            $this->reply('Could not find Slack ID in your message. Please make sure you mention the user and ask me something like: What is the Slack ID of @mention');
        }
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
