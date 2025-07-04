<?php

namespace App\Objects;

use App\Enums\MessageType;
use App\Models\Users\AbstractUser;
use App\Models\Users\BotUser;

class MessageObject
{
    public function __construct(
        public readonly AbstractUser $from,
        public ?AbstractUser $to,
        public readonly string $message,
        public readonly array $meta = [],
        public readonly MessageType $type = MessageType::TEXT,
    ) {
        if (blank($to)) {
            $this->setTo();
        }
    }

    private function setTo(): void
    {
        if ($activeConversation = $this->from->user->activeConversation) {
            $this->to = $activeConversation->getParticipants()->reject($this->from)->first();
        } else {
            $this->to = BotUser::fromRequest();
        }
    }
}
