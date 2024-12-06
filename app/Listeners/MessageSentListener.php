<?php

namespace App\Listeners;

use Musonza\Chat\Eventing\MessageWasSent;

class MessageSentListener
{
    /**
     * Handle the event.
     */
    public function handle(MessageWasSent $event): void
    {
        $recipient = $event->message->conversation->getParticipants()->reject($event->message->sender)->first();
        $recipient->consume($event->message);
    }
}
