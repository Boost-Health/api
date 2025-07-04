<?php

namespace App\Http\Controllers\Api\Channels\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramRequest;
use App\Http\Resources\ConversationResponseResource;
use App\Models\Users\TelegramUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;

class WebhookController extends Controller
{
    public function __invoke(string $token, TelegramRequest $request, ConversationService $conversationService)
    {
        $telegramUser = TelegramUser::fromRequest(request()->all());
        $conversation = $conversationService->message(new MessageObject(
            from: $telegramUser,
            to: $telegramUser->getRecipientInActiveConversation(),
            message: request('message.text'),
            meta: request()->all()
        ));

        return new ConversationResponseResource($conversation);
    }
}
