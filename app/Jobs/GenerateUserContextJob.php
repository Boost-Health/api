<?php

namespace App\Jobs;

use App\Enums\PromptCode;
use App\Models\BotUser;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Musonza\Chat\Models\Conversation;
use Musonza\Chat\Models\Message;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class GenerateUserContextJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Conversation $conversation, private readonly User $user)
    {

    }

    public function handle(): void
    {
        $prompt = Prompt::for(PromptCode::GENERATE_USER_CONTEXT);
        $existingContext = $this->user->context ?? "<existing_context>EMPTY</existing_context>";
        $messages = $this->getFormattedMessagesForContext();

        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt("{$prompt} \n\n{$existingContext}\n\n<messages>\n{$messages}\n</messages>")
            ->asText();

        $this->user->update([
            'context' => $response->text,
            'context_last_generated_chat_message_id' => $this->conversation->last_message()->first()->id,
            'context_last_generated_at' => now()
        ]);
    }

    private function getFormattedMessagesForContext(): string
    {
        $messages = $this
            ->conversation
            ->messages()
            ->where('body', 'not like', 'REQUIRES_HUMAN')
            ->when(
                $this->user->context_last_generated_chat_message_id > 0,
                fn ($query) => $query->where('id', '>=', $this->user->context_last_generated_chat_message_id)
            )
            ->orderByDesc('id')
            ->get()
            ->sortBy('id');

        return $messages->map(function (Message $message) {
            return $message->sender instanceof BotUser
                ? sprintf('Doctor: %s\n', $message->body)
                : sprintf('User: %s\n', $message->body);
        })
            ->join("\n");
    }
}
