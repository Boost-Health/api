<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\Models\BotUser;
use App\Models\TelegramUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(string $token, TelegramRequest $request, ConversationService $conversationService)
    {
        Log::info('telegram:request', request()->all());

        $boostHealthToken = config('telegram.bots.BoostHealth.token');
        if ($token !== $boostHealthToken) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }

        $conversation = $conversationService->message(new MessageObject(
            TelegramUser::fromRequest(request()->all()),
            BotUser::fromRequest(),
            request('message.text'),
            request()->all()
        ));

        return response()->json([
            'status' => 'Successful',
            'message' => 'Request Successful',
            'data' => [
                'conversation' => $conversation,
            ],
        ]);
    }
}
