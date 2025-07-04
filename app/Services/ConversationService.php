<?php

namespace App\Services;

use App\Objects\MessageObject;
use Exception;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Exceptions\DirectMessagingExistsException;
use Musonza\Chat\Exceptions\InvalidDirectMessageNumberOfParticipants;
use Musonza\Chat\Facades\ChatFacade;
use Musonza\Chat\Models\Conversation;

class ConversationService
{
    public function message(MessageObject $messageObject): Conversation
    {
        try {
            $conversation = ChatFacade::conversations()->between($messageObject->from, $messageObject->to)
                ?? ChatFacade::createConversation([$messageObject->from, $messageObject->to])->makeDirect();

            $this->setActiveConversation($conversation, $messageObject);
            ChatFacade::message($messageObject->message)->from($messageObject->from)->to($conversation)->send();

            return $conversation;
        } catch (DirectMessagingExistsException|InvalidDirectMessageNumberOfParticipants|Exception $e) {
            Log::error($e);
        }

        return new Conversation;
    }

    private function setActiveConversation(Conversation $conversation, MessageObject $messageObject): void
    {
        if ($messageObject->from->user->isNotBot()) {
            $messageObject->from->user->update(['active_conversation_id' => $conversation->id]);
        }

        if ($messageObject->to->user->isNotBot()) {
            $messageObject->to->user->update(['active_conversation_id' => $conversation->id]);
        }
    }
}
