<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'update_id' => ['required', 'integer'],
            'message' => ['required', 'array'],
            'message.message_id' => ['required', 'integer'],
            'message.from' => ['required', 'array'],
            'message.from.id' => ['required', 'integer'],
            'message.from.is_bot' => ['required', 'boolean'],
            'message.chat' => ['required', 'array'],
            'message.chat.id' => ['required', 'integer'],
            'message.date' => ['required', 'integer'],
            'message.text' => ['string'],
        ];
    }
}
