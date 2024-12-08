<?php

namespace App\FlowCharts;

use App\Enums\PromptCode;
use App\Models\BotUser;
use App\Models\Prompt;
use App\Objects\FlowChartNextObject;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\ValueObjects\Messages\AssistantMessage;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Musonza\Chat\Models\Message;

final class PersonalHealthFlowChart extends BaseFlowChart
{
    public function init(): FlowChartNextObject
    {
        $messages = $this->conversation->messages->map(function (Message $message) {
            return $message->sender instanceof BotUser
                ? new AssistantMessage($message->body)
                : new UserMessage($message->body);
        })
            ->toArray();

        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withSystemPrompt(Prompt::for(PromptCode::MEDICAL_HELP))
            ->withMessages($messages)
            ->generate();

        return new FlowChartNextObject('init', [$response->text]);
    }
}
