<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\Models\BotUser;
use App\Models\TelegramUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;

class BotController extends Controller
{
    public function __invoke(TelegramRequest $request, ConversationService $conversationService)
    {
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
