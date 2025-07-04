<?php

namespace App\Http\Requests;

use App\Enums\SlackEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SlackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'event' => ['required', Rule::enum(SlackEvent::class)],
            'message.user' => ['required'],
            'message.type' => ['required'],
            'message.ts' => ['required'],
            'message.client_msg_id' => ['required'],
            'message.text' => ['required'],
            'message.team' => ['required'],
            'message.channel' => ['required'],
            'message.event_ts' => ['required'],
            'message.channel_type' => ['required'],
        ];
    }
}
