<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;

class SetWebhookController extends Controller
{
    public function __invoke()
    {
        $webhookUrl = url(sprintf('/api/bot/telegram/%s/webhook', config('telegram.bots.BoostHealth.token')));
        if (request('show_webhook_url')) {
            return response()->json([
                'status' => 'Successful',
                'message' => 'Request successful',
                'data' => [
                    'webhook_url' => $webhookUrl,
                    'http_test' => Http::get('https://pages.onepipe.io/health')->json(),
                ],
            ]);
        }

        $response = Telegram::setWebhook(['url' => $webhookUrl]);

        return response()->json([
            'status' => 'Successful',
            'message' => 'Request successful',
            'data' => [
                'webhook_response' => $response,
            ],
        ]);
    }
}
