<?php

namespace App\Http\Controllers\Api\Channels\Slack;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlackRequest;
use App\Models\Users\SlackChannelUser;
use App\Models\Users\SlackUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(SlackRequest $request, ConversationService $conversationService)
    {
        try {
            $conversation = $conversationService->message(new MessageObject(
                SlackUser::fromRequest(request()->all()),
                SlackChannelUser::fromRequest(request()->all()),
                request('text'),
                request()->all()
            ));

            return response()->json([
                'status' => 'Successful',
                'message' => 'Request Successful',
                'data' => [
                    'conversation' => $conversation,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('slack:webhook:error', ['error' => $e->getMessage()]);

            return response()->json(null, 500);
        }
    }
}
