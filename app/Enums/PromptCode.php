<?php

namespace App\Enums;

enum PromptCode: string
{
    case MEDICAL_HELP = 'MEDICAL_HELP';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (PromptCode $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
