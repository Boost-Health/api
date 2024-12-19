<?php

namespace App\Models;

use App\Enums\UserType;
use App\Jobs\ProcessMessageJob;
use Musonza\Chat\Models\Message;

class BotUser extends AbstractUser
{
    protected $casts = [
        'meta' => 'json',
    ];

    public static function fromRequest(array $payload = []): BotUser
    {
        if ($bot = self::first()) {
            return $bot;
        }

        $user = User::updateOrCreate(['type' => UserType::BOT], [
            'first_name' => 'Bot',
            'last_name' => 'Man',
            'type' => UserType::BOT,
        ]);

        $user->bot()->create(['name' => 'Bot', 'meta' => ['llm' => 'GPT-4o']]);

        return $bot;
    }

    public function consume(Message $message): void
    {
        ProcessMessageJob::dispatch($message);
    }
}
