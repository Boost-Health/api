<?php

namespace App\Services;

use App\Objects\MessageObject;
use Musonza\Chat\Facades\ChatFacade;
use Musonza\Chat\Models\Conversation;

class ConversationService
{
    public function message(MessageObject $messageObject): Conversation
    {
        $conversation = ChatFacade::conversations()->between($messageObject->from, $messageObject->to)
            ?? ChatFacade::createConversation([$messageObject->from, $messageObject->to])->makeDirect();

        ChatFacade::message($messageObject->message)->from($messageObject->from)->to($conversation)->send();

        return $conversation;
    }
}
