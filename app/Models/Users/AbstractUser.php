<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Musonza\Chat\Models\Message;
use Musonza\Chat\Traits\Messageable;

/**
 * @property mixed $from
 * @property mixed $user
 */
abstract class AbstractUser extends Model
{
    use Messageable;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    abstract public static function fromRequest(array $payload): AbstractUser;

    abstract public function consume(Message $message): void;

    public function getRecipientInActiveConversation(): AbstractUser
    {
        if ($activeConversation = $this->user->activeConversation) {
            return $activeConversation
                ->getParticipants()
                ->reject(fn ($participant) => $participant->is($this))
                ->first();
        }

        return BotUser::fromRequest();
    }
}
