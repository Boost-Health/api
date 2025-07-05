<?php

namespace App\Enums;

enum PromptCode: string
{
    case MEDICAL_HELP = 'MEDICAL_HELP';
    case REWRITE = 'REWRITE';
    case GENERATE_USER_CONTEXT = 'GENERATE_USER_CONTEXT';
    case AI_MENTION_CHECK_CONVERSATION_END = 'AI_MENTION_CHECK_CONVERSATION_END';
    case AI_MENTION_ASK_FOR_SLACK_ID = 'AI_MENTION_ASK_FOR_SLACK_ID';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (PromptCode $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
