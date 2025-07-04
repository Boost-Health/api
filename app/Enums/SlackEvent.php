<?php

namespace App\Enums;

enum SlackEvent: string
{
    case PATIENT_REPLY = 'patient.reply';
    case AI_MENTION = 'ai.mention';
}
