<?php

use App\Http\Controllers\Api\Channels\Telegram\BotController;
use App\Http\Controllers\Api\Channels\Telegram\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'status' => 'Successful',
    'message' => 'Request Successful',
    'data' => null,
]));

Route::group(['prefix' => '/bot'], function () {
    Route::group(['prefix' => 'telegram'], function () {
        Route::any('/', BotController::class);
        Route::any('/{token}/webhook', WebhookController::class);
    });
});
