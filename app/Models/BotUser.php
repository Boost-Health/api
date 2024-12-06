<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
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

        $user = User::updateOrCreate(['is_bot' => true], [
            'first_name' => 'Bot',
            'last_name' => 'Man',
            'is_bot' => true,
        ]);

        $user->bot()->create(['name' => 'Bot', 'meta' => ['llm' => 'GPT-4o']]);

        return $bot;
    }

    public function consume(Message $message): void
    {
        Log::info('bot:user:consume', $message->toArray());
    }
}
