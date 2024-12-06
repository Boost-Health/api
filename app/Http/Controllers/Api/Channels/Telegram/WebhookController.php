<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Telegram\Bot\Laravel\Facades\Telegram;

class WebhookController extends Controller
{
    public const SET_WEBHOOK = false;

    public function __invoke(string $token, Request $request)
    {
        $boostHealthToken = config('telegram.bots.BoostHealth.token');
        if ($token !== $boostHealthToken) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        if (self::SET_WEBHOOK) {
            Telegram::setWebhook(['url' => url(sprintf('/api/bot/telegram/%s/webhook', $boostHealthToken))]);
        }

        return response()->json(true);
    }
}
