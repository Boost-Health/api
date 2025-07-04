<?php

namespace App\Http\Controllers\Api\Channels\Slack;

use App\Enums\SlackEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\SlackRequest;
use App\Http\Resources\ConversationResponseResource;
use App\Jobs\SlackAIMentionJob;
use App\Models\Users\SlackChannelUser;
use App\Models\Users\SlackUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(SlackRequest $request)
    {
        try {
            return match (request('event')) {
                SlackEvent::PATIENT_REPLY->value => $this->patientConversation(),
                SlackEvent::AI_MENTION->value => $this->aiMention(),
                default => throw new Exception('Invalid event type.'),
            };
        } catch (Exception $e) {
            Log::error('slack:webhook:error', ['error' => $e->getMessage()]);

            return response()->json(null, 500);
        }
    }

    protected function patientConversation(): ConversationResponseResource
    {
        $conversation = app(ConversationService::class)
            ->message(new MessageObject(
                SlackUser::fromRequest(request('message')),
                SlackChannelUser::fromRequest(request('message')),
                request('message.text'),
                request('message')
            ));

        return new ConversationResponseResource($conversation);
    }

    protected function aiMention(): JsonResponse
    {
        $messageObject = new MessageObject(
            SlackUser::fromRequest(request('message')),
            SlackChannelUser::fromRequest(request('message')),
            request('message.text'),
            request('message')
        );

        SlackAIMentionJob::dispatch($messageObject);

        return response()->json();
    }
}
