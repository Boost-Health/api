<?php

namespace App\Enums;

enum PromptCode: string
{
    case MEDICAL_HELP = 'MEDICAL_HELP';
    case SUMMARIZE_CONVERSATION_FOR_DOCTOR = 'SUMMARIZE_CONVERSATION_FOR_DOCTOR';
    case REWRITE = 'REWRITE';
    case GENERATE_USER_CONTEXT = 'GENERATE_USER_CONTEXT';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (PromptCode $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
