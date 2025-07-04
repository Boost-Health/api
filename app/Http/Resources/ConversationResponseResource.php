<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @method getParticipants()
 */
class ConversationResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'status' => 'Successful',
            'message' => 'Request Successful',
            'data' => [
                'conversation' => [
                    'id' => $this->id,
                    'private' => $this->private,
                    'direct_message' => $this->direct_message,
                    'data' => $this->data,
                    'created_at' => $this->created_at,
                    'participants' => $this->getParticipants()
                        ->map(fn ($participant) => [
                            'type' => $participant::class,
                            'id' => $participant->id,
                            'user_id' => $participant->user->id,
                            'user_type' => $participant->user->type,
                        ])
                        ->toArray(),
                ],
            ],
        ];
    }
}
