<?php

namespace App\Enums;

enum PromptCode: string
{
    case ONBOARD_NEW_USERS = 'ONBOARD_NEW_USERS';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (PromptCode $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
