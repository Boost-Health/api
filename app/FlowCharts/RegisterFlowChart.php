<?php

namespace App\FlowCharts;

use App\Clients\OpenMRSClient;
use App\Clients\SlackBotClient;
use App\Enums\PromptCode;
use App\Jobs\GenerateUserContextJob;
use App\Models\Prompt;
use App\Objects\FlowChartNextObject;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use RuntimeException;
use Throwable;

final class RegisterFlowChart extends BaseFlowChart
{
    public static function rewrite(string $statement): string
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.providers.openai.model'))
            ->withSystemPrompt(Prompt::for(PromptCode::REWRITE, ['statement' => $statement]))
            ->asText();

        return $response->text;
    }

    public function init(): FlowChartNextObject
    {
        $message = self::rewrite(
            sprintf(
                "Hello %s! I'm here to help you manage your health and wellness. To get started, could you share some basic information about yourself? This will help me provide personalized advice. Please answer as much as you're comfortable with 😀",
                $this->user->first_name
            )
        );

        return new FlowChartNextObject(
            'gender',
            [
                $message,
                self::rewrite('Lets start with your Gender. I do like to know if you are Male or Female?'),
            ]
        );
    }

    protected function gender(): FlowChartNextObject
    {
        $gender = strtolower(trim($this->message->body));
        if (! in_array($gender, ['m', 'male', 'f', 'female'])) {
            return new FlowChartNextObject('gender', ['Please reply with Male or Female 😑']);
        }

        return new FlowChartNextObject(
            'birthDate',
            [
                self::rewrite('Awesome. How about your Date of birth? e.g 1990-12-05'),
            ],
            ['gender' => str_contains($gender, 'f') ? 'F' : 'M']
        );
    }

    protected function birthDate(): FlowChartNextObject
    {
        try {
            Carbon::createFromFormat('Y-m-d', $this->message->body);
        } catch (Throwable $th) {
            Log::error('flow-chart:register', ['error' => $th->getMessage(), 'step' => 'birthDate']);

            return new FlowChartNextObject(
                'birthDate',
                [
                    'Please enter your date of birth in format Y-m-d, eg 1990-10-20',
                ]
            );
        }

        return new FlowChartNextObject(
            'phone',
            [
                'What is your phone number?',
            ],
            ['birthDate' => $this->message->body]
        );
    }

    protected function phone(): FlowChartNextObject
    {
        if (preg_match('/^(0\d{10}|234\d{10}|\+234\d{11})$/', $this->message->body)) {
            return new FlowChartNextObject(
                'medicalConditions',
                [
                    self::rewrite('Do you currently have any known medical conditions or allergies? You can reply me No if you do not have, otherwise please tell me about it'),
                ],
                ['phone' => $this->message->body]
            );
        }

        return new FlowChartNextObject('phone', ['Please enter a valid phone number. Eg 08100000000'], ['birthDate' => $this->message->body]);
    }

    protected function medicalConditions(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'medications',
            [
                self::rewrite('Are you currently on any medications? If No, just reply me no, otherwise feel free to tell me about the medications, perhaps their names if you remember'),
            ],
            ['medicalConditions' => $this->message->body]
        );
    }

    protected function medications(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'end',
            [
                self::rewrite('Do you have any lifestyle habits I should be aware of? Smoking? Frequently exercise? Sitting a lot? Give me an idea about your lifestyle'),
            ],
            ['medications' => $this->message->body]
        );
    }

    protected function end(): FlowChartNextObject
    {
        try {
            $this->createOpenMRSUser();
            dispatch(new GenerateUserContextJob($this->conversation, $this->user))->onConnection('sync');

            $this->user->update(['is_onboarded' => true, 'phone' => Arr::get($this->conversation->data, 'phone')]);

            $response = app(SlackBotClient::class)->patientRegister($this->user);
            if ($response->failed()) {
                throw new RuntimeException('Could not create slack channel', $response->json());
            }

            $data = $response->json();
            $this->user->update([
                'slack_channel_id' => Arr::get($data, 'data.channel.id'),
                'slack_channel_name' => Arr::get($data, 'data.channel.name'),
            ]);
        } catch (Throwable $th) {
            Log::error('flowchart:register:error', ['message' => $th->getMessage()]);
        }

        return new FlowChartNextObject(
            null,
            [
                self::rewrite(sprintf('Alright. That\'s about it for now. Thank you %s!', $this->user->first_name)),
                self::rewrite('You can now ask me Personal health and wellness questions. I will try my best to assist you and may connect you to a doctor if need be.'),
            ],
            ['lifestyle' => $this->message->body]
        );
    }

    protected function createOpenMRSUser(): void
    {
        if ((int) config('services.open-mrs.enabled') === 0) {
            return;
        }

        $response = app(OpenMRSClient::class)->createPatient($this->user, $this->conversation);
        $this->user->update([
            'open_mrs_patient_uuid' => Arr::get($response->json(), 'uuid'),
            'meta' => [
                'create_patient_status_code' => $response->status(),
                'create_patient_response' => $response->json(),
            ],
        ]);
    }
}
