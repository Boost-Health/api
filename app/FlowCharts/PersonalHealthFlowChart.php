<?php

namespace App\FlowCharts;

use App\Enums\PromptCode;
use App\Jobs\GenerateUserContextJob;
use App\Models\Prompt;
use App\Models\User;
use App\Models\Users\BotUser;
use App\Notifications\NotifyAdminsOfUnavailableDoctorsNotification;
use App\Notifications\NotifyDoctorNotification;
use App\Objects\FlowChartNextObject;
use Illuminate\Support\Str;
use Musonza\Chat\Models\Message;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

final class PersonalHealthFlowChart extends BaseFlowChart
{
    public const COMMAND_REQUIRES_HUMAN = 'REQUIRES_HUMAN';

    public function init(): FlowChartNextObject
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt(Prompt::for(PromptCode::MEDICAL_HELP, ['existing_user_context' => $this->user->context]))
            ->withMessages($this->getFormattedMessagesForPrism())
            ->asText();

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

    private function getFormattedMessagesForPrism(): array
    {
        $messages = $this
            ->conversation
            ->messages()
            ->where('body', 'not like', 'REQUIRES_HUMAN')
            ->where('id', '>', $this->user->context_last_generated_chat_message_id)
            ->get();

        return $messages->map(function (Message $message) {
            return $message->sender instanceof BotUser
                ? new AssistantMessage($message->body)
                : new UserMessage($message->body);
        })
            ->toArray();
    }

    public function requiresHumanCallback(): string
    {
        GenerateUserContextJob::dispatch($this->conversation, $this->user, true);

        if ($doctor = User::availableDoctor()) {
            $this->user->inviteToSlackChannel($doctor);
            $doctor->notify(new NotifyDoctorNotification($this->conversation, $this->user));

            return sprintf('Alright. Doctor %s has been contacted. You will be contacted within an hour.', $doctor->name);
        }

        if ($user = User::whereEmail('boosthealthlimited@gmail.com')->first()) {
            $user->notify(new NotifyDoctorNotification($this->conversation, $this->user));
            $user->notify(new NotifyAdminsOfUnavailableDoctorsNotification($this->user));
        }

        return 'Sorry, All our Doctors are currently busy. Please try again later';
    }
}
