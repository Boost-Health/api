<?php

namespace App\Listeners;

use App\Clients\SlackBotClient;
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

        $this->notifySlack($event, $recipient);
    }

    private function notifySlack(MessageWasSent $event, $recipient): void
    {
        if ($event->message->sender->user->isBot()) {
            app(SlackBotClient::class)->aiMessage($recipient->user, $event->message->body);
        } else {
            app(SlackBotClient::class)->patientMessage($event->message->sender->user, $event->message->body);
        }
    }
}
