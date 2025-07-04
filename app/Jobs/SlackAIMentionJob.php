<?php

namespace App\Jobs;

use App\Enums\PromptCode;
use App\Models\Prompt;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class SlackAIMentionJob implements ShouldQueue
{
    use Conditionable, Queueable;

    public function __construct(private readonly MessageObject $messageObject) {}

    public function handle(ConversationService $conversationService): void
    {
        $this
            ->when($this->wantsToEndConversation(), fn () => $conversationService->endConversation($this->messageObject));
    }

    private function wantsToEndConversation(): bool
    {
        $reply = preg_replace('/(<@[^>]+>\s*)+/', '', $this->messageObject->message);
        $prompt = Prompt::for(PromptCode::AI_MENTION_CHECK_CONVERSATION_END, ['reply' => $reply]);

        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt($prompt)
            ->asText();

        Log::info('slack-ai-mention:wants-to-end-conversation', ['reply' => $reply, 'response' => $response->text]);

        return Str::contains($response->text, 'TRUE');
    }
}
