<?php

namespace App\FlowCharts;

use App\Enums\PromptCode;
use App\Models\BotUser;
use App\Models\Prompt;
use App\Models\User;
use App\Notifications\NotifyAdminsOfUnavailableDoctorsNotification;
use App\Notifications\NotifyDoctorNotification;
use App\Objects\FlowChartNextObject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Musonza\Chat\Models\Conversation;
use Musonza\Chat\Models\Message;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

final class PersonalHealthFlowChart extends BaseFlowChart
{
    public const MAXIMUM_NUMBER_OF_RECENT_MESSAGES_FOR_CONTEXT = 10;

    public const COMMAND_REQUIRES_HUMAN = 'REQUIRES_HUMAN';

    public function init(): FlowChartNextObject
    {
        $messages = self::getFormattedMessagesForPrism($this->conversation, self::MAXIMUM_NUMBER_OF_RECENT_MESSAGES_FOR_CONTEXT);
        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withSystemPrompt(Prompt::for(PromptCode::MEDICAL_HELP))
            ->withMessages($messages)
            ->generate();

        $responseText = $response->text;
        if ($commandCallback = $this->getCommandCallback($responseText)) {
            $callbackResponse = $this->{$commandCallback}();
            if ($callbackResponse) {
                $responseText = $callbackResponse;
            }
        }

        return new FlowChartNextObject('init', [$responseText]);
    }

    private function getCommandCallback(?string $text): ?string
    {
        return match (true) {
            Str::contains($text, self::COMMAND_REQUIRES_HUMAN, true) => 'requiresHumanCallback',
            default => null
        };
    }

    public static function getFormattedMessagesForPrism(Conversation $conversation, int $limit, bool $raw = false): array
    {
        $messages = $conversation
            ->messages()
            ->where('body', 'not like', 'REQUIRES_HUMAN')
            ->take($limit)
            ->orderByDesc('id')
            ->get()
            ->sortBy('id');

        if ($raw) {
            return $messages->toArray();
        }

        return $messages->map(function (Message $message) {
            return $message->sender instanceof BotUser
                ? new AssistantMessage($message->body)
                : new UserMessage($message->body);
        })
            ->toArray();
    }

    public function requiresHumanCallback(): string
    {
        if ($doctor = User::availableDoctor()) {
            $this->user->inviteToSlackChannel($doctor);
            $doctor->notify(new NotifyDoctorNotification($this->conversation, $this->user));

            return sprintf('Alright. Doctor %s has been contacted. You will be contacted within an hour.', $doctor->name);
        }

        Log::warning("personal:health:doctors:busy:{$this->conversation->id}");
        if ($user = User::whereEmail('boosthealthlimited@gmail.com')->first()) {
            $user->notify(new NotifyDoctorNotification($this->conversation, $this->user));
            $user->notify(new NotifyAdminsOfUnavailableDoctorsNotification($this->user));
        }

        return 'Sorry, All our Doctors are currently busy. Please try again later';
    }
}
