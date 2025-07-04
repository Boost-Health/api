<?php

namespace App\Models\Users;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Musonza\Chat\Models\Message;

class SlackChannelUser extends AbstractUser
{
    protected $table = 'users';

    protected $casts = [
        'type' => UserType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public static function fromRequest(array $payload = []): SlackChannelUser
    {
        return static::whereSlackChannelId(Arr::get($payload, 'channel'))->whereType(UserType::USER)->firstOrFail();
    }

    public function consume(Message $message): void
    {
        $clonedMessage = clone $message;
        $clonedMessage->body = sprintf("_[%s]_\n\n%s", $message->sender->user->name, $message->body);

        $this->user->telegram->consume($clonedMessage);
    }
}
