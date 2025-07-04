<?php

namespace App\Objects;

use App\Enums\MessageType;
use App\Models\Users\AbstractUser;

class MessageObject
{
    public function __construct(
        public readonly AbstractUser $from,
        public AbstractUser $to,
        public string $message,
        public readonly array $meta = [],
        public readonly MessageType $type = MessageType::TEXT,
    ) {}
}
