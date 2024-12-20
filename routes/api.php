<?php

use App\Http\Controllers\Api\Channels\Telegram\SetWebhookController;
use App\Http\Controllers\Api\Channels\Telegram\WebhookController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;

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

    Log::channel('papertrail')->info('LOG:CHANNEL');
    Log::channel('stdout')->info('Test log for Stdout');

    $log = new Logger('papertrail');
    $handler = new SyslogUdpHandler('logs5.papertrailapp.com', 49643);
    $log->pushHandler($handler);

    $log->info('LOG:MANUAL');

    return response()->json([
        'app' => config('app'),
        'logging' => config('logging'),
        'env' => $_ENV,
    ]);
});

Route::get('/exception', fn () => throw new \Exception('Testing Sentry'));
