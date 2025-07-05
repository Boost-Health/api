<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case DOCTOR_PRESCRIBED = 'doctor_prescribed';
    case ORDER_IN_PROGRESS = 'order_in_progress';
    case COMPLETED = 'completed';

    public static function getAsOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (ConsultationStatus $code) => [$code->value => ucwords(strtolower(implode(' ', explode('_', $code->name))))])
            ->toArray();
    }
}
