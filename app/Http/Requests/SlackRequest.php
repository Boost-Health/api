<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user' => ['required'],
            'type' => ['required'],
            'ts' => ['required'],
            'client_msg_id' => ['required'],
            'text' => ['required'],
            'team' => ['required'],
            'channel' => ['required'],
            'event_ts' => ['required'],
            'channel_type' => ['required'],
        ];
    }
}
