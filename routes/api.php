<?php

use App\Http\Controllers\Api\Channels\Telegram\SetWebhookController;
use App\Http\Controllers\Api\Channels\Telegram\WebhookController;
use App\Http\Middleware\SlackWebhookMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', static fn () => response()->json([
    'status' => 'Successful',
    'message' => 'Request Successful',
    'data' => null,
]));

Route::group(['prefix' => '/bot'], static function () {
    Route::group(['prefix' => 'telegram'], static function () {
        Route::any('/set-webhook', SetWebhookController::class);
        Route::any('/{token}/webhook', WebhookController::class);
    });

    Route::group(['prefix' => 'slack'], static function () {
        Route::any('/webhook', \App\Http\Controllers\Api\Channels\Slack\WebhookController::class);
    })->middleware(SlackWebhookMiddleware::class);
});
