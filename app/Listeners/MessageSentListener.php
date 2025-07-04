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
        $slackClient = app(SlackBotClient::class);

        if ($event->message->sender->user->isBot()) {
            $slackClient->aiMessage($recipient->user, $event->message->body);
        }

        if ($event->message->sender->user->isUser()) {
            $slackClient->patientMessage($event->message->sender->user, $event->message->body);
        }
    }
}
