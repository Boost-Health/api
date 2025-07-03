<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
        'bot' => [
            'token' => env('SLACK_TOKEN', '74f90abc-5c3f-4fd8-9904-22129129f245'),
            'patient_bot_base_url' => env('SLACK_PATIENT_BOT_BASE_URL', 'https://patient-slack-bot.onrender.com'),
            'ai_bot_base_url' => env('SLACK_PATIENT_BOT_BASE_URL', 'https://ai-slack-bot-vy12.onrender.com'),
        ]
    ],
    'open-mrs' => [
        'enabled' => env('OPEN_MRS_ENABLED', 0),
        'base_url' => env('OPEN_MRS_BASE_URL', 'https://clinic.boosthealthinc.com/openmrs/ws/rest/v1'),
        'username' => env('OPEN_MRS_USERNAME', 'admin'),
        'password' => env('OPEN_MRS_USERNAME', 'Admin123'),
    ],
];
