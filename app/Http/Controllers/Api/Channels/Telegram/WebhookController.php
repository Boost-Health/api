<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __invoke(string $token, Request $request)
    {
        $boostHealthToken = config('telegram.bots.BoostHealth.token');
        if ($token !== $boostHealthToken) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        return response()->json(true);
    }
}
