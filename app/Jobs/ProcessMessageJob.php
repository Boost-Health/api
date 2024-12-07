<?php

namespace App\Jobs;

use App\FlowCharts\BaseFlowChart;
use App\FlowCharts\DefaultFlowChart;
use App\FlowCharts\RegisterFlowChart;
use App\Models\BotUser;
use App\Objects\MessageObject;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Musonza\Chat\Models\Message;

class ProcessMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Message $message) {}

    public function handle(): void
    {
        $flowChartClass = Arr::get($this->message->conversation->data, '_flowChart', RegisterFlowChart::class);

        /** @var BaseFlowChart $flowChart */
        $flowChart = new $flowChartClass($this->message->sender->user, $this->message);
        if ($this->message->sender->user->is_onboarded && $flowChart instanceof RegisterFlowChart) {
            $flowChart = new DefaultFlowChart($this->message->sender->user, $this->message);
        }

        $response = $flowChart->next();
        foreach ($response->responses as $response) {
            app(ConversationService::class)->message(new MessageObject(
                BotUser::fromRequest(),
                $this->message->sender,
                $response
            ));

            sleep(1);
        }
    }
}
