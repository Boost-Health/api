<?php

namespace App\Models\Users;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Message;

class SlackUser extends AbstractUser
{
    protected $table = 'users';

    protected $casts = [
        'type' => UserType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public static function fromRequest(array $payload = []): SlackUser
    {
        return static::whereSlackUserId(Arr::get($payload, 'user'))->where('type', '!=', UserType::USER)->firstOrFail();
    }

    public function consume(Message $message): void
    {
        Log::info("slack:user:{$this->user->id}:consume", $message->toArray());
    }
}
