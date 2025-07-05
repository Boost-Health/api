<?php

namespace App\Enums;

enum OrderType: string
{
    case SELF_PURCHASE = 'self_purchase';
    case DELIVERY = 'delivery';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (OrderType $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
