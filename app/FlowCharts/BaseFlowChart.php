<?php

namespace App\FlowCharts;

use App\Models\User;
use App\Objects\FlowChartNextObject;
use Exception;
use Illuminate\Support\Arr;
use Musonza\Chat\Models\Conversation;
use Musonza\Chat\Models\Message;

abstract class BaseFlowChart
{
    public readonly Conversation $conversation;

    public function __construct(public User $user, public Message $message)
    {
        $this->conversation = $this->message->conversation;
    }

    public function next()
    {
        $nextStep = Arr::get($this->conversation->data, '_next', 'init');
        if (! method_exists($this, $nextStep)) {
            throw new Exception(sprintf('Method %s does not exist on FlowChart instance:%s', $nextStep, static::class));
        }

        /** @var FlowChartNextObject $response */
        $response = $this->{$nextStep}();

        $flowChartData = ['_next' => $response->next];
        if ($response->next === null) {
            $flowChartData['_flowChart'] = null;
        }

        $updatedConversationData = array_filter(array_merge(
            $this->conversation->data ?? [],
            $flowChartData,
            $response->newData
        ));

        $this->conversation->update(['data' => $updatedConversationData]);

        return $response;
    }

    abstract public function init(): FlowChartNextObject;
}
