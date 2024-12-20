<?php

use App\Http\Controllers\Api\Channels\Telegram\SetWebhookController;
use App\Http\Controllers\Api\Channels\Telegram\WebhookController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'status' => 'Successful',
    'message' => 'Request Successful',
    'data' => null,
]));

Route::group(['prefix' => '/bot'], function () {
    Route::group(['prefix' => 'telegram'], function () {
        Route::any('/set-webhook', SetWebhookController::class);
        Route::any('/{token}/webhook', WebhookController::class);
    });
});

Route::get('/log', function () {
    Log::error('test:log', ['context' => 'test', 'config' => config('app')]);

    return response()->json([
        'app' => config('app'),
        'logging' => config('logging'),
        'env' => $_ENV,
    ]);
});

Route::get('/exception', fn () => throw new \Exception('Testing Sentry'));
