<?php

namespace App\Console\Commands;

use App\Enums\UserType;
use App\Models\User;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Console\Command;

class EndStaleDoctorConversationCommand extends Command
{
    private const NUMBER_OF_HOURS_TO_CHECK_FOR_STALE = 1;

    protected $signature = 'app:conversations:stale:end';

    protected $description = 'Find stale conversations that a doctor is part of and end it';

    public function handle(): void
    {
        $doctorsWithActiveConversations = User::whereType(UserType::DOCTOR)
            ->whereNotNull('active_conversation_id')
            ->get();

        foreach ($doctorsWithActiveConversations as $doctorWithActiveConversation) {
            $this->info("stale:conversation:processing:{$doctorWithActiveConversation->id}");

            $this->check($doctorWithActiveConversation);
        }

        if (blank($doctorsWithActiveConversations)) {
            $this->info('no:stale:conversations');
        }
    }

    private function check(User $doctor): void
    {
        $conversationId = $doctor->activeConversation->id;
        $lastMessage = $doctor->activeConversation->last_message;
        $doctorParticipant = $doctor->activeConversation->getParticipants()->filter(fn ($participant) => $participant->user->is($doctor))->first();
        $userParticipant = $doctor->activeConversation->getParticipants()->reject(fn ($participant) => $participant->user->is($doctor))->first();

        if ($lastMessage?->created_at->diffInHours(now()) >= self::NUMBER_OF_HOURS_TO_CHECK_FOR_STALE) {
            $messageObject = new MessageObject(
                from: $doctorParticipant,
                to: $userParticipant,
                message: 'Stale Conversation',
                meta: ['stale' => true]
            );

            app(ConversationService::class)->endConversation($messageObject);

            $this->info('stale:conversation:ended');
        }
    }
}
