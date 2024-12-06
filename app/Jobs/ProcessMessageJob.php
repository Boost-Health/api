<?php

namespace App\Jobs;

use App\Enums\PromptCode;
use App\Models\BotUser;
use App\Models\Prompt;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Message;

class ProcessMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Message $message) {}

    public function handle(): void
    {
        app(ConversationService::class)->message(new MessageObject(
            BotUser::fromRequest(),
            $this->message->sender,
            Prompt::for(PromptCode::ONBOARD_NEW_USERS)
        ));

        Log::info('job:process-message', $this->message->toArray());
    }
}
