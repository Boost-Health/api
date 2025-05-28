<?php

namespace App\Clients;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SlackBotClient
{
    public PendingRequest $patientBotHttp;
    public PendingRequest $aiBotHttp;

    public function __construct(array $config)
    {
        $this->patientBotHttp = Http::asJson()
            ->acceptJson()
            ->withToken($config['token'])
            ->baseUrl($config['patient_bot_base_url']);

        $this->aiBotHttp = Http::asJson()
            ->acceptJson()
            ->withToken($config['token'])
            ->baseUrl($config['ai_bot_base_url']);
    }

    private function message(PendingRequest $http, User $user, string $message): Response
    {
        $payload = [
            'channel_id' => $user->slack_channel_id,
            'message' => $message
        ];

        return $http->post('message', $payload);
    }

    public function patientMessage(User $user, string $message): Response
    {
        return $this->message($this->patientBotHttp, $user, $message);
    }

    public function aiMessage(User $user, string $message): Response
    {
        return $this->message($this->aiBotHttp, $user, $message);
    }

    public function patientRegister(User $user): Response
    {
        $payload = [
            'id' => $user->id,
            'name' => "$user->first_name $user->last_name",
        ];

        return $this->patientBotHttp->post('register', $payload);
    }

}
