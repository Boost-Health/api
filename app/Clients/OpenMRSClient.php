<?php

namespace App\Clients;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Musonza\Chat\Models\Conversation;

class OpenMRSClient
{
    public PendingRequest $http;

    public function __construct(array $config)
    {
        $this->http = Http::asJson()
            ->acceptJson()
            ->withBasicAuth($config['username'], $config['password'])
            ->baseUrl($config['base_url']);
    }

    public function createPatient(User $user, Conversation $conversation): Response
    {
        $data = $conversation->data;
        $birthDate = Arr::get($data, 'birthDate');
        $payload = [
            'identifiers' => [
                [
                    'identifier' => (string) $user->id,
                    'identifierType' => '05a29f94-c0ed-11e2-94be-8c13b969e334',
                    'location' => '58c57d25-8d39-41ab-8422-108a0c277d98',
                    'preferred' => true,
                ],
            ],
            'person' => [
                'gender' => Arr::get($data, 'gender', 'M'),
                'age' => (int) Carbon::parse($birthDate)->age,
                'birthdate' => (string) $birthDate,
                'birthdateEstimated' => false,
                'dead' => false,
                'deathDate' => null,
                'causeOfDeath' => null,
                'names' => [
                    [
                        'givenName' => (string) $user->first_name,
                        'familyName' => (string) $user->last_name,
                    ],
                ],
                'addresses' => [
                    [
                        'address1' => 'Redworth Terraces 2, Ikate Lekki',
                        'cityVillage' => 'Lekki',
                        'country' => 'Nigeria',
                        'postalCode' => '1001234',
                    ],
                ],
            ],
        ];

        Log::info('open:mrs:create:patient', $payload);

        return $this->http->post('patient', $payload);
    }
}
