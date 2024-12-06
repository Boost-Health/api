<?php

namespace App\Objects;

use App\Enums\MessageType;
use App\Models\AbstractUser;

class MessageObject
{
    public function __construct(
        public readonly AbstractUser $from,
        public readonly AbstractUser $to,
        public readonly string $message,
        public readonly array $meta = [],
        public readonly MessageType $type = MessageType::TEXT,
    ) {}
}
