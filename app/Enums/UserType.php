<?php

namespace App\Enums;

enum UserType: string
{
    case BOT = 'bot';
    case USER = 'user';
    case DOCTOR = 'doctor';
}
