<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Message;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramUser extends AbstractUser
{
    protected $casts = [
        'meta' => 'json',
    ];

    public static function fromRequest(array $payload): TelegramUser
    {
        $name = [
            'first_name' => Arr::get($payload, 'message.from.first_name'),
            'last_name' => Arr::get($payload, 'message.from.last_name'),
        ];

        $telegram = [
            'telegram_chat_id' => Arr::get($payload, 'message.chat.id'),
            'meta' => $payload,
        ];

        $telegramUser = self::whereTelegramId(Arr::get($payload, 'message.from.id'))->first();
        if ($telegramUser) {
            $telegramUser->user->update($name);
            $telegramUser->update([
                'telegram_chat_id' => Arr::get($payload, 'message.chat.id'),
                'meta' => $payload,
            ]);
        } else {
            $user = User::create($name);
            $telegramUser = $user->telegram()->create([
                'telegram_id' => Arr::get($payload, 'message.from.id'),
                ...$telegram,
            ]);
        }

        return $telegramUser;
    }

    public function consume(Message $message): void
    {
        $messageObject = [
            'chat_id' => $this->telegram_chat_id,
            'text' => $message->body,
            'parse_mode' => 'HTML',
        ];

        if (app()->environment('local')) {
            Log::info("telegram:message:user:{$this->user->id}:sent", $messageObject);

            return;
        }

        try {
            Telegram::sendMessage($messageObject);
        } catch (TelegramSDKException $e) {
            Log::error('telegram:message:error', ['message' => $message->toArray(), 'error' => $e->getMessage()]);
        }
    }
}
